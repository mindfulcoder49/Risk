<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameTerritory;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GameActionController extends Controller
{
    // Note: Add authorization to ensure the acting player is the current turn player.
    private function authorizePlayer(Game $game)
    {
        $player = $game->players()->where('user_id', Auth::id())->first();
        if (!$player || $game->current_turn_player_id !== $player->id) {
            abort(403, 'It is not your turn.');
        }
        return $player;
    }

    public function claimTerritory(Request $request, Game $game)
    {
        $player = $this->authorizePlayer($game);
        if ($game->turn_phase !== 'claim') {
            abort(403, 'Not in claim phase.');
        }

        $validated = $request->validate([
            'territory_id' => 'required|integer|exists:territories,id',
        ]);

        // Check if territory is already claimed
        $isClaimed = GameTerritory::where('game_id', $game->id)
            ->where('territory_id', $validated['territory_id'])
            ->exists();

        if ($isClaimed) {
            return back()->withErrors(['claim' => 'This territory is already claimed.']);
        }

        DB::transaction(function () use ($game, $player, $validated) {
            // Claim the territory
            GameTerritory::create([
                'game_id' => $game->id,
                'player_id' => $player->id,
                'territory_id' => $validated['territory_id'],
                'armies' => 1,
            ]);

            // Check if all territories are now claimed
            $totalTerritories = \App\Models\Territory::count();
            $claimedTerritories = $game->gameTerritories()->count();

            if ($claimedTerritories >= $totalTerritories) {
                // All territories claimed, move to setup reinforcement phase
                $firstPlayer = $game->players()->orderBy('turn_order')->first();
                $game->update([
                    'turn_phase' => 'setup_reinforce',
                    'current_turn_player_id' => $firstPlayer->id,
                ]);
            } else {
                // Advance to the next player
                $nextPlayer = $game->players()
                    ->where('turn_order', '>', $player->turn_order)
                    ->orderBy('turn_order')
                    ->first() ?? $game->players()->orderBy('turn_order')->first();

                $game->update([
                    'current_turn_player_id' => $nextPlayer->id,
                ]);
            }
        });

        return back();
    }

    public function placeSetupArmies(Request $request, Game $game)
    {
        $player = $this->authorizePlayer($game);
        if ($game->turn_phase !== 'setup_reinforce') {
            abort(403, 'Not in setup reinforcement phase.');
        }

        $validated = $request->validate([
            'reinforcements' => 'required|array',
            'reinforcements.*.territory_id' => 'required|integer|exists:territories,id',
            'reinforcements.*.armies' => 'required|integer|min:1',
        ]);

        // Calculate how many armies the player should be placing
        $playerCount = $game->players()->count();
        $startingArmies = [2 => 40, 3 => 35, 4 => 30, 5 => 25, 6 => 20][$playerCount];
        $territoriesOwned = $player->gameTerritories()->count();
        $requiredArmies = $startingArmies - $territoriesOwned;

        $requestedArmies = collect($validated['reinforcements'])->sum('armies');

        if ($requestedArmies !== $requiredArmies) {
            return back()->withErrors(['reinforcements' => 'You must place exactly ' . $requiredArmies . ' armies.']);
        }

        DB::transaction(function () use ($validated, $player, $game) {
            foreach ($validated['reinforcements'] as $placement) {
                $gameTerritory = GameTerritory::where('game_id', $game->id)
                    ->where('territory_id', $placement['territory_id'])
                    ->where('player_id', $player->id)
                    ->firstOrFail();

                $gameTerritory->increment('armies', $placement['armies']);
            }

            // Find the next player
            $nextPlayer = $game->players()
                ->where('turn_order', '>', $player->turn_order)
                ->orderBy('turn_order')
                ->first();

            if ($nextPlayer) {
                // Go to the next player's setup turn
                $game->update(['current_turn_player_id' => $nextPlayer->id]);
            } else {
                // Last player finished, start the actual game
                $firstPlayer = $game->players()->orderBy('turn_order')->first();
                $game->update([
                    'current_turn_player_id' => $firstPlayer->id,
                    'turn_phase' => 'reinforce',
                ]);
            }
        });

        return back();
    }

    public function reinforce(Request $request, Game $game)
    {
        $player = $this->authorizePlayer($game);
        if ($game->turn_phase !== 'reinforce') {
            abort(403, 'Not in reinforcement phase.');
        }

        // Clear the battle log at the start of the turn
        $request->session()->forget('battle_log');

        $validated = $request->validate([
            'reinforcements' => 'required|array',
            'reinforcements.*.territory_id' => 'required|integer|exists:territories,id',
            'reinforcements.*.armies' => 'required|integer|min:1',
        ]);

        // Calculate total reinforcements available
        $gameController = new GameController();
        $totalReinforcements = (int) $gameController->calculateReinforcements($game);
        $requestedReinforcements = collect($validated['reinforcements'])->sum('armies');

        if ($requestedReinforcements !== $totalReinforcements) {
            return back()->withErrors(['reinforcements' => 'You must place exactly ' . $totalReinforcements . ' armies.']);
        }

        DB::transaction(function () use ($validated, $player, $game) {
            foreach ($validated['reinforcements'] as $placement) {
                $gameTerritory = GameTerritory::where('game_id', $game->id)
                    ->where('territory_id', $placement['territory_id'])
                    ->where('player_id', $player->id)
                    ->firstOrFail(); // Fails if player doesn't own territory

                $gameTerritory->increment('armies', $placement['armies']);
            }

            $game->update(['turn_phase' => 'attack']);
        });

        return back()->with('success', 'Reinforcements placed. You may now attack.');
    }

    public function attack(Request $request, Game $game)
    {
        $player = $this->authorizePlayer($game);
        if ($game->turn_phase !== 'attack') {
            abort(403, 'Not in attack phase.');
        }

        // If player chooses to skip attack and go to fortify phase
        if ($request->input('action') === 'skip') {
            $game->update(['turn_phase' => 'fortify']);
            return back();
        }

        $validated = $request->validate([
            'from_territory_id' => 'required|integer|exists:game_territories,territory_id,game_id,'.$game->id,
            'to_territory_id' => 'required|integer|exists:game_territories,territory_id,game_id,'.$game->id,
            'armies' => 'required|integer|min:1|max:3',
        ]);

        $from = GameTerritory::with('territory.adjacencies') // Eager load the relationship
            ->where('game_id', $game->id)
            ->where('territory_id', $validated['from_territory_id'])
            ->firstOrFail();
        $to = GameTerritory::where('game_id', $game->id)->where('territory_id', $validated['to_territory_id'])->firstOrFail();

        // Validation
        if ($from->player_id !== $player->id || $to->player_id === $player->id) {
            abort(403, 'Invalid attack target.');
        }
        if ($from->armies <= $validated['armies']) {
            return back()->withErrors(['armies' => 'You must leave at least one army behind.']);
        }
        if (!$from->territory->adjacencies->contains('id', $validated['to_territory_id'])) {
            abort(403, 'Territories are not adjacent.');
        }

        DB::transaction(function () use ($from, $to, $validated, $player, $game, $request) {
            // Dice rolls
            $attackerDice = min($validated['armies'], 3);
            $defenderDice = min($to->armies, 2);

            $attackerRolls = collect(range(1, $attackerDice))->map(fn () => rand(1, 6))->sortDesc()->values();
            $defenderRolls = collect(range(1, $defenderDice))->map(fn () => rand(1, 6))->sortDesc()->values();

            $attackerLosses = 0;
            $defenderLosses = 0;

            for ($i = 0; $i < min($attackerDice, $defenderDice); $i++) {
                if ($attackerRolls[$i] > $defenderRolls[$i]) {
                    $defenderLosses++;
                } else {
                    $attackerLosses++;
                }
            }

            $attackerRollsStr = $attackerRolls->join(', ');
            $defenderRollsStr = $defenderRolls->join(', ');
            $message = "Attack on {$to->territory->name}: Attacker rolled ({$attackerRollsStr}), Defender rolled ({$defenderRollsStr}). Attacker lost {$attackerLosses}, Defender lost {$defenderLosses}.";

            $from->decrement('armies', $attackerLosses);
            $to->decrement('armies', $defenderLosses);

            // Check for conquest
            if ($to->armies <= 0) {
                $defendingPlayer = $to->player;
                $to->update([
                    'player_id' => $player->id,
                    'armies' => $validated['armies'] - $attackerLosses
                ]);
                $from->decrement('armies', $validated['armies'] - $attackerLosses);

                $message .= " You have conquered {$to->territory->name}!";

                // Check for player elimination
                if ($defendingPlayer->gameTerritories()->count() === 0) {
                    $defendingPlayer->update(['status' => 'eliminated']);
                }

                // Check for game win condition
                $activePlayers = $game->players()->where('status', 'active')->count();
                if ($activePlayers === 1) {
                    $game->update([
                        'status' => 'finished',
                        'winner_player_id' => $player->id,
                        'turn_phase' => null,
                    ]);
                }
            }
            // Push the message to the session log
            $request->session()->push('battle_log', $message);
        });

        return back();
    }

    public function fortify(Request $request, Game $game)
    {
        $player = $this->authorizePlayer($game);
        if ($game->turn_phase !== 'fortify') {
             abort(403, 'Not in fortify phase.');
        }

        $validated = $request->validate([
            'from_territory_id' => 'nullable|integer|exists:game_territories,territory_id,game_id,'.$game->id,
            'to_territory_id' => 'nullable|integer|exists:game_territories,territory_id,game_id,'.$game->id,
            'armies' => 'nullable|integer|min:1',
        ]);

        // Handle fortification move if data is provided
        if (!empty($validated['from_territory_id']) && !empty($validated['to_territory_id']) && !empty($validated['armies'])) {
            $from = GameTerritory::where('game_id', $game->id)->where('territory_id', $validated['from_territory_id'])->firstOrFail();
            $to = GameTerritory::where('game_id', $game->id)->where('territory_id', $validated['to_territory_id'])->firstOrFail();

            if ($from->player_id !== $player->id || $to->player_id !== $player->id) {
                abort(403, 'You do not own both territories.');
            }
            if ($from->armies <= $validated['armies']) {
                return back()->withErrors(['armies' => 'Not enough armies to move.']);
            }
            // Basic adjacency check for simplification. A full check requires graph traversal.
            $from->territory->load('adjacencies');
            if (!$from->territory->adjacencies->contains($validated['to_territory_id'])) {
                 return back()->withErrors(['fortify' => 'You can only fortify adjacent territories.']);
            }

            DB::transaction(function () use ($from, $to, $validated) {
                $from->decrement('armies', $validated['armies']);
                $to->increment('armies', $validated['armies']);
            });

            $message = "You moved {$validated['armies']} armies from {$from->territory->name} to {$to->territory->name}.";
            // After a successful fortification, we end the turn.
        }

        // Advance to the next player's turn
        DB::transaction(function () use ($game, $player) {
            $nextPlayer = $game->players()
                ->where('status', 'active')
                ->where('turn_order', '>', $player->turn_order)
                ->orderBy('turn_order')
                ->first();

            if (!$nextPlayer) {
                $nextPlayer = $game->players()
                    ->where('status', 'active')
                    ->orderBy('turn_order')
                    ->first();
            }

            $game->update([
                'current_turn_player_id' => $nextPlayer->id,
                'turn_phase' => 'reinforce',
            ]);
        });

        // Clear the log for the next player
        $request->session()->forget('battle_log');

        return back()->with('success', $message . ' Your turn has ended.');
    }

    public function endAttackPhase(Request $request, Game $game)
    {
        $player = $this->authorizePlayer($game);
        if ($game->turn_phase !== 'attack') {
            abort(403, 'Not in attack phase.');
        }

        $game->update(['turn_phase' => 'fortify']);

        return back()->with('success', 'Attack phase ended. You may now fortify.');
    }
}
