<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Get all games the user is a player in, loading necessary relationships.
        $games = Game::whereHas('players', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['players.user', 'currentTurnPlayer.user'])
        ->latest()
        ->get();

        $activeGames = $games->where('status', 'playing')->values();
        $waitingGames = $games->where('status', 'waiting')->values();

        return Inertia::render('Dashboard', [
            'activeGames' => $activeGames,
            'waitingGames' => $waitingGames,
        ]);
    }
}
