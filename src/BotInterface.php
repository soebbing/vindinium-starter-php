<?php

namespace Vindinium;

use Vindinium\Structs\Board;
use Vindinium\Structs\State;

interface BotInterface
{
    /**
     * @param State $state
     * @return Board::South|Board::North|Board::West|Board::East|Board::Stay
     */
    public function move(State $state);
}