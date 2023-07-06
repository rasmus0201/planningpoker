<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show()
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        return ApiResponse::success($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        $user->update([
            'email' => $request->email,
            'username' => $request->username,
        ]);

        return ApiResponse::success($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        $user->games()->forceDelete();
        $user->gameVotes()->forceDelete();
        $user->tokens()->forceDelete();
        $user->forceDelete();

        $request->session()->regenerate();

        return ApiResponse::success();
    }
}
