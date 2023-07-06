<?php

namespace App\Http\Resources;

use App\Models\GameParticipant;
use Illuminate\Http\Resources\Json\JsonResource;

class GameParticipantResource extends JsonResource
{
    public function __construct(GameParticipant $model)
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
            'username' => $this->whenLoaded('user', fn () => $this->user->username, ''),
        ];
    }
}
