<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameTerritory;
use App\Models\Player;
use App\Models\Territory;
use App\Models\Continent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GameController extends Controller
{
    // Available colors for players
    private const PLAYER_COLORS = ['#FF0000', '#0000FF', '#008000', '#FFFF00', '#FFA500', '#800080'];

    public function index()
    {
        // Show a list of games in 'waiting' status
        return Inertia::render('Games/Index', [
            'games' => Game::where('status', 'waiting')->with('players.user')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $game = DB::transaction(function () use ($request) {
            $game = Game::create([
                'name' => $request->name,
            ]);

            $game->players()->create([
                'user_id' => Auth::id(),
                'color' => self::PLAYER_COLORS[0],
                'turn_order' => 1,
            ]);

            return $game;
        });

        return redirect()->route('games.show', $game);
    }

    public function join(Game $game)
    {
        // Logic to add the current user to the game if not full
        if ($game->status !== 'waiting') {
            return back()->with('error', 'This game has already started.');
        }

        if ($game->players()->count() >= 6) {
            return back()->with('error', 'This game is full.');
        }

        if ($game->players()->where('user_id', Auth::id())->exists()) {
            return back()->with('error', 'You have already joined this game.');
        }

        DB::transaction(function () use ($game) {
            $turnOrder = $game->players()->max('turn_order') + 1;
            $color = self::PLAYER_COLORS[$game->players()->count()];

            $game->players()->create([
                'user_id' => Auth::id(),
                'color' => $color,
                'turn_order' => $turnOrder,
            ]);
        });

        return redirect()->route('games.show', $game);
    }

    public function start(Game $game)
    {
        // Logic for the host to start the game.
        $player = $game->players()->where('user_id', Auth::id())->first();

        // Only the first player (host) can start the game
        if (!$player || $player->turn_order !== 1) {
            return back()->with('error', 'Only the host can start the game.');
        }

        if ($game->status !== 'waiting') {
            return back()->with('error', 'Game has already started.');
        }

        $playerCount = $game->players()->count();
        if ($playerCount < 2) {
            return back()->with('error', 'You need at least 2 players to start.');
        }

        DB::transaction(function () use ($game, $playerCount) {
            // 1. Set game status to 'playing'.
            $game->status = 'playing';

            // 2. Set the turn to the player with turn_order 1 and phase to 'claim'.
            $players = $game->players()->orderBy('turn_order')->get();
            $game->current_turn_player_id = $players->first()->id;
            $game->turn_phase = 'claim';
            $game->save();
        });

        return redirect()->route('games.show', $game);
    }

    public function show(Game $game)
    {
        // This is the main endpoint for viewing a game
        $game->load([
            'players.user',
            'gameTerritories.territory.continent',
            'gameTerritories.territory.adjacencies',
            'gameTerritories.player', // Ensure player is loaded on gameTerritory
            'currentTurnPlayer.user',
            'winner.user'
        ]);

        // We need to transform the territory data into a format GeoJSON can easily use.
        $territories = Territory::with('adjacencies')->get();

        $setupArmies = 0;
        if ($game->turn_phase === 'setup_reinforce') {
            $playerCount = $game->players()->count();
            $startingArmies = [2 => 40, 3 => 35, 4 => 30, 5 => 25, 6 => 20][$playerCount];
            $territoriesOwned = $game->currentTurnPlayer->gameTerritories()->count();
            $setupArmies = $startingArmies - $territoriesOwned;
        }

        return Inertia::render('Games/Show', [
            'game' => $game,
            'map' => [
                'territories' => $territories, // Static map data
            ],
            // We need to calculate this on the fly for the current player
            'reinforcements' => $this->calculateReinforcements($game),
            'setupArmies' => $setupArmies,
            'authPlayerData' => [
                'player' => $game->players()->where('user_id', Auth::id())->first()
            ],
            'battleLog' => session('battle_log', []),
        ]);
    }

    public function calculateReinforcements(Game $game): int
    {
        if ($game->status !== 'playing' || $game->turn_phase !== 'reinforce') {
            return 0;
        }

        $player = $game->currentTurnPlayer;
        if (!$player) {
            return 0;
        }

        // Calculate reinforcements from territories
        $territoryCount = $player->gameTerritories()->count();
        $reinforcements = max(3, floor($territoryCount / 3));

        // Calculate reinforcements from continents
        $continents = Continent::with('territories')->get();
        foreach ($continents as $continent) {
            $continentTerritoryIds = $continent->territories->pluck('id');
            $playerTerritoryCountInContinent = $player->gameTerritories()
                ->whereIn('territory_id', $continentTerritoryIds)
                ->count();

            if ($continentTerritoryIds->count() === $playerTerritoryCountInContinent) {
                $reinforcements += $continent->army_bonus;
            }
        }

        return $reinforcements;
    }
}
