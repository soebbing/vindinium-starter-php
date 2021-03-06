<?php

namespace Vindinium\Structs;

use Vindinium\Structs\Game;
use Vindinium\Structs\Hero;

/**
 * Describes the state of the game, the board incl. everything and everyone on it.
 */
class State
{
    /** @var string */
    private $token;

    /** @var string */
    private $viewUrl;

    /** @var string */
    private $playUrl;

    /** @var Game */
    private $game;

    /** @var Hero */
    private $hero;

    /** @var array */
    private $route;

    /**
     * @param array $json
     * @return State
     * @throws \RuntimeException
     */
    public static function fromJson(array $json)
    {
        $state = new State();
        foreach ($json as $key => $value) {
            $state->$key = $value;

            if ($key === 'game') {
                $state->game = Game::fromJson($value);
            }
            if ($key === 'hero') {
                $state->hero = Hero::fromJson($value);
            }
        }

        if (null === $state->getGame()) {
            throw new \RuntimeException('State has no game');
        }

        if (null === $state->getHero()) {
            throw new \RuntimeException('State has no hero');
        }

        return $state;
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

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @return Hero
     */
    public function getHero()
    {
        return $this->hero;
    }

    /**
     * @return array
     */
    public function getRoute(): ?array
    {
        return $this->route;
    }

    /**
     * @param array $route
     */
    public function setRoute(array $route): void
    {
        $this->route = $route;
    }
}