<?php

namespace Vindinium\Structs;

use Vindinium\Structs\Hero;
use Vindinium\Structs\Board;
use Vindinium\Structs\Position;

class Game
{
    /** @var string */
    private $id;

    /** @var int */
    private $turn;

    /** @var int */
    private $maxTurns;

    /** @var Hero */
    private $hero;

    /** @var Hero[] */
    private $heroes;

    /** @var Board */
    private $board;

    /**
     * @param array $json
     * @return Game
     */
    public static function fromJson(array $json)
    {
        $game = new Game();
        foreach ($json as $key => $value) {
            if ('board' === $key) {
                $game->$key = 'Vindinium\Structs\\' . ucfirst($key)::fromJson($value);
            }

            if ('heros' === $key) {
                $elements = [];
                foreach ($value as $element) {
                    $elements = 'Vindinium\Structs\\' . $value::fromJson($element);
                }
                $game->$key = $elements;
            }

            $game->$key = $value;
        }

        return $game;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * @return int
     */
    public function getMaxTurns()
    {
        return $this->maxTurns;
    }

    /**
     * @return Hero
     */
    public function getHero()
    {
        return $this->hero;
    }

    /**
     * @return Hero[]
     */
    public function getHeroes()
    {
        return $this->heroes;
    }

    /**
     * @return Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return $this->viewUrl;
    }

    /**
     * @return string
     */
    public function getPlayUrl()
    {
        return $this->playUrl;
    }
}