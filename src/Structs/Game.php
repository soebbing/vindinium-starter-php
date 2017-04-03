<?php

namespace Vindinium\Structs;

use Vindinium\Structs\Board;

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

    /** @var bool */
    private $finished;

    /**
     * @param array $json
     * @return Game
     */
    public static function fromJson(array $json)
    {
        $game = new Game();
        foreach ($json as $key => $value) {
            $game->$key = $value;

            if ($key === 'board') {
                $game->board = Board::fromJson($value);
            }

            if ($key === 'hero') {
                $game->hero = Hero::fromJson($value);
            }

            if ('heroes' === $key) {
                $elements = [];
                foreach ($value as $element) {
                    $elements[] = Hero::fromJson($element);
                }
                $game->$key = $elements;
            }

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
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished;
    }
}