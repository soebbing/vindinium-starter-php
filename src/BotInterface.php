<?php

namespace Vindinium;

use Vindinium\Structs\State;

interface BotInterface
{
    /**
     * @param State $state
     * @return mixed
     */
    public function move(State $state);
}