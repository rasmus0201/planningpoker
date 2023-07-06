<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function __construct(Game $model)
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
            'userId' => $this->user_id,
            'pin' => $this->pin,
            'state' => $this->state,
            'title' => $this->title,
            'createdAt' => $this->created_at,

            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            // 'rounds' => $this->whenLoaded('rounds', fn () => $this->rounds),
            // 'latestRound' => $this->whenLoaded('latestRound', fn () => $this->latestRound),
        ];
    }
}
