<?php

namespace App\Enums;

enum GameState: string
{
    case Lobby = 'lobby';
    case Voting = 'voting';
    case Revealing = 'revealing';
    case Finished = 'finished';
}
