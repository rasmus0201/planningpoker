<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function __construct(User $model)
    {
        $this->resource = $model;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'createdAt' => $this->created_at,

            'games' => $this->whenLoaded('games', fn () => GameResource::collection($this->games)),
            'gameVotes' => $this->whenLoaded('gameVotes', fn () => GameVoteResource::collection($this->gameVotes)),
        ];
    }
}
