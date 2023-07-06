<?php

use App\Http\Controllers\{
    Auth\AuthenticationController,
    Auth\ForgotPasswordController,
    ExportMeController,
    GameController,
    GameParticipantController,
    GameVoteController,
    MeController
};
use App\Http\Middleware\UserLastActiveMiddleware;
use App\Models\Game;
use Illuminate\Support\Facades\{Auth, Broadcast, Route};

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('games.{pin}', function ($user, $pin) {
    if (Auth::check()) {
        $game = Game::where('pin', $pin)->first();

        $participant = $user->participant($game);

        return [
            'socketId' => request('socket_id'),
            'broadcastingId' => $user->getAuthIdentifierForBroadcasting(),
            'userId' => $user->id,
            'participantId' => $participant?->id,
            'kickedAt' => $participant?->kicked_at,
            'username' => $user->username,
            'joinType' => request('join_type'),
        ];
    }
});

Route::prefix('/auth')->group(function () {
    Route::post('/register', [AuthenticationController::class, 'register'])->name('register');
    Route::post('/login', [AuthenticationController::class, 'login'])->name('login');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgot'])->name('password.forgot');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.reset');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', UserLastActiveMiddleware::class])->group(function () {
    Route::prefix('/me')->group(function () {
        Route::get('/', [MeController::class, 'show']);
        Route::patch('/', [MeController::class, 'update']);
        Route::delete('/', [MeController::class, 'destroy']);
        Route::post('/export', ExportMeController::class);
    });

    Route::apiResource('games', GameController::class);

    Route::apiResource('games.participants', GameParticipantController::class)->only(['index', 'store', 'update']);
    Route::apiResource('games.votes', GameVoteController::class)->only(['store']);
});
