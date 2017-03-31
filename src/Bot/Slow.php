<?php

namespace Vindinium\Bot;

use Vindinium\BotInterface;
use Vindinium\Structs\State;

class Slow implements BotInterface
{
    public function move(State $state)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');
        sleep(2);
        return $dirs[array_rand($dirs)];
    }
}