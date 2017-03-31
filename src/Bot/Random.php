<?php

namespace Vindinium\Bot;

use Vindinium\BotInterface;
use Vindinium\Structs\State;

class Random implements BotInterface
{
    /**
     * @param State $state
     * @return mixed
     */
    public function move(State $state)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');

        return $dirs[array_rand($dirs)];
    }
}