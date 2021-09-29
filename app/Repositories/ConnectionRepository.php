<?php

namespace App\Repositories;

use App\Log;
use App\Models\Connection;
use Illuminate\Database\Eloquent\Collection;

class ConnectionRepository extends AbstractRepository
{
    public function getConnections(int $gameId): Collection
    {
        return Connection::where('game_id', $gameId)->get();
    }

    public function getByClientId(string $clientId): ?Connection
    {
        return Connection::where('client_id', $clientId)->first();
    }

    public function create(
        int $resourceId,
        string $clientId,
        ?int $gameId = null,
        ?int $userId = null
    ): void {
        try {
            Connection::updateOrCreate(
                [
                    'client_id' => $clientId,
                ],
                [
                    'id' => $resourceId,
                    'client_id' => $clientId,
                    'game_id' => $gameId,
                    'user_id' => $userId,
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() !== 23000) {
                Log::get()->warning($e->getMessage() . "\n" . $e->getTraceAsString());
            }
        }
    }

    public function syncByIds(array $ids): void
    {
        Connection::with('user')->whereNotIn('id', $ids)->get()->each(function (Connection $conn) {
            $conn->user()->update([
                'connection_id' => null
            ]);

            $conn->delete();
        });
    }
}
