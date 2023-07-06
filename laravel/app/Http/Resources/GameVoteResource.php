<?php

namespace App\Http\Resources;

use App\Models\GameVote;
use Illuminate\Http\Resources\Json\JsonResource;

class GameVoteResource extends JsonResource
{
    public function __construct(GameVote $model)
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
            'participantId' => $this->game_participant_id,
            'vote' => $this->vote,
            'createdAt' => $this->created_at,

            'username' => $this->whenLoaded('participant', fn () => $this->participant->user->username),
        ];
    }
}
