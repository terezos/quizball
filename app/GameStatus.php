<?php

namespace App;

enum GameStatus: string
{
    case Waiting = 'waiting';
    case Active = 'active';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
}
