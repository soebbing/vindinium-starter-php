<?php

namespace Vindinium\Bot;

use Vindinium\BotInterface;
use Vindinium\Parser\TileParser;
use Vindinium\Structs\State;

class LordHelmchen implements BotInterface
{
    /**
     * @param State $state
     * @return mixed
     */
    public function move(State $state)
    {
        $tileParser = new TileParser();
        $tiles = $tileParser->parse($state->getGame());

        var_dump($tiles);die;
    }
}