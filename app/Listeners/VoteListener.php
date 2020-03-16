<?php

namespace App\Listeners;

use App\Database;

class VoteListener extends Listener
{
    public function listen()
    {
        return 'message:vote';
    }

    public function handle()
    {
        $stmt = Database::run(
            'SELECT * FROM users WHERE clientId = :clientId LIMIT 1',
            [
                ':clientId' => $this->event->data['data']['clientId'] ?? ''
            ]
        );

        if (!$user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return;
        }

        Database::run("INSERT INTO votes (user_id, vote_id) VALUES (:user_id, :vote_id)", [
            ':user_id' => (int) $user['id'],
            ':vote_id' => (int) $this->event->data['data']['vote']
        ]);

        $this->event->sendSubscribers([
            'type' => 'vote',
            'data' => [
                'username' => $this->event->data['data']['username'] ?? '',
            ]
        ]);

        $this->sendShowoffIfAllVoted();
    }

    private function sendShowoffIfAllVoted()
    {
        // Check if last vote, if so send the answers.
        $countConnectedUsers = Database::run("SELECT COUNT(*) as count FROM users WHERE connected = 1 AND clientId IS NOT NULL")->fetch(\PDO::FETCH_ASSOC);
        $votes = Database::run("SELECT u.username, v.vote_id FROM votes v
            INNER JOIN users u ON u.id = v.user_id
            WHERE u.connected = 1
            AND u.clientId IS NOT NULL
        ")->fetchAll(\PDO::FETCH_ASSOC);

        if (((int) $countConnectedUsers['count']) !== count($votes)) {
            return;
        }

        $this->event->sendPublisher([
            'type' => 'showoff',
            'data' => $votes
        ]);

        $this->event->sendSubscribers([
            'type' => 'showoff',
            'data' => $votes
        ]);
    }
}
