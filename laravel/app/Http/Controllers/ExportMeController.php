<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;

class ExportMeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $user = $this->user();
        $user->load(['games', 'gameVotes']);

        return ApiResponse::success($user);
    }
}
