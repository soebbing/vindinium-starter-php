<?php

namespace Vindinium\Bot;

use Vindinium\BotInterface;
use Vindinium\Parser\TileParser;
use Vindinium\Service\Astar;
use Vindinium\Structs\Board;
use Vindinium\Structs\Hero;
use Vindinium\Structs\State;
use Vindinium\Structs\Position;
use Vindinium\Structs\Interpreted\Tile;

class LordHelmchen implements BotInterface
{
    /** @var Astar */
    private $astar;

    /** @var TileParser */
    private $tileParser;

    /** @var string */
    private $lastDirection;

    /** @var array */
    private $route;

    /** @var array */
    private $cache;

    /**
     * @param Astar $astar
     * @param TileParser $tileParser
     */
    public function __construct(Astar $astar, TileParser $tileParser)
    {
        $this->astar = $astar;
        $this->tileParser = $tileParser;
    }

    /**
     * @param State $state
     *
     * @throws \OutOfBoundsException
     *
     * @return mixed
     */
    public function move(State $state): string
    {
        $tiles = $this->tileParser->parse($state);
        $this->astar->setState($state, $tiles);

        $this->cache = [];
        foreach ($tiles as $tile) {
            $this->cache[$tile->getID()] = $tile;
        }

        $this->route = $this->findRoute($state, $tiles);

        $this->lastDirection = $this->getDirection($state->getHero(), $this->route);
        $state->setRoute($this->route);

        return $this->lastDirection;
    }

    /**
     * Core logic that decides what is the next move.
     *
     * @param State $state
     * @param Tile[] $tiles
     *
     * @throws \OutOfBoundsException
     *
     * @return Tile[]
     */
    private function findRoute(State $state, array $tiles): array
    {
        $targetTiles = $this->buildTargetList($state, $tiles);

        /**
         * Contains a list of possible target tiles, the steps to that tile and the weight of the target
         */
        $targets = [];

        foreach ($targetTiles as $tile) {
            // Determine the steps to said target
            $steps = $this->astar->run($this->getTileForPosition($state->getHero()->getPosition()), $tile);
            $targets[] = [
                'weight' => 0, # lower is better
                'tile' => $tile,
                'steps' => $steps,
            ];
        }

        foreach ($targets as &$target) {
            $target['weight'] = $this->weightTile($target['tile'], $target['steps'], $state);
        }
        unset($target);

        // Sort the list by weights, asc
        usort($targets, function($a, $b) {
            return $a['weight'] > $b['weight'] ? 1 : -1;
        });

        $targetString = array_map(function ($target) {
            return $target['weight'] . ' Gewicht, ' .
                count($target['steps']) . ' Schritte zu ' .
                $target['tile']->getType();
        }, $targets);

        echo implode(PHP_EOL, $targetString);

        $target = array_shift($targets);


        return $target['steps'];
    }

    /**
     * Find all relevant target nodes
     *
     * @param State $state
     * @param array $tiles
     *
     * @throws \OutOfBoundsException
     *
     * @return Tile[]
     */
    private function buildTargetList(State $state, array $tiles): array
    {
        $otherHeros = $this->getHeros($state->getHero(), $state->getGame()->getHeroes());
        $targets = $this->getTavernsAndTreasures($state->getHero(), $tiles);

        return array_merge($targets, $otherHeros);
    }

    /**
     * @param Hero $hero
     * @param Tile[] $tiles
     * @return Tile[]
     */
    private function getTavernsAndTreasures(Hero $hero, array $tiles): array
    {
        $targets = [];

        foreach ($tiles as $tile) {
            if ($tile->getType() === Tile::TAVERN ||
                ($tile->getType() === Tile::TREASURE &&
                (!$tile->getOwner() || ($tile->getOwner()->getPosition() !== $hero->getPosition())))) {
                $targets[] = $tile;
            }
        }

        return $targets;
    }

    /**
     * @param Hero $lordHelmchen
     * @param Hero[] $heroes
     *
     * @throws \OutOfBoundsException
     *
     * @return Tile[]
     */
    private function getHeros(Hero $lordHelmchen, array $heroes): array
    {
        $heros = [];

        /** @var Hero $hero */
        foreach ($heroes as $hero) {
            if ((string) $hero->getPosition() === (string) $lordHelmchen->getPosition()) {
                continue;
            }

            $heros[] = $this->getTileForPosition($hero->getPosition());
        }

        return $heros;
    }

    /**
     * Get the direction for a given target node.
     *
     * @param Hero $hero
     * @param array $steps
     * @return string
     */
    private function getDirection(Hero $hero, array $steps): string
    {
        array_shift($steps); // The first element is the starting position, we remove it.
        $targetNode = array_shift($steps);

        $currentNode = $hero->getPosition();
        if ($currentNode->getX() > $targetNode->getX()) {
            return Board::North;
        }

        if ($currentNode->getX() < $targetNode->getX()) {
            return Board::South;
        }

        if ($currentNode->getY() > $targetNode->getY()) {
            return Board::West;
        }

        if ($currentNode->getY() < $targetNode->getY()) {
            return Board::East;
        }

        return Board::Stay;
    }

    /**
     * Return the Tile object for a given Position
     *
     * @param Position $position
     *
     * @throws \OutOfBoundsException
     *
     * @return Tile
     */
    private function getTileForPosition(Position $position): Tile
    {
        if (!array_key_exists($position->getID(), $this->cache)) {
            throw new \OutOfBoundsException('No tile for node "' . $position->getID() . '" found.');
        }

        return $this->cache[$position->getID()];
    }

    /**
     * @return array
     */
    public function getRoute(): array
    {
        return $this->route;
    }

    private function weightTile(Tile $tile, array $steps, State $state): float
    {
        switch ($tile->getType()) {
            case Tile::HERO:
                return $this->weightHero($tile, $steps, $state);

            case Tile::TAVERN:
                return $this->weightTavern($tile, $steps, $state);

            case Tile::TREASURE:
                return $this->weightTreasure($tile, $steps, $state);
        }

        return \count($steps);
    }

    private function weightTavern(Tile\Tavern $tile, array $steps, State $state)
    {
        $weight = \count($steps);
/*
        # If we have enough life, we don't want to go to a tavern
        if ($state->getHero()->getLife() > 25) {
            return $weight + 50;
        }

        # If we DON'T have enough life, we don't want to go to a tavern
        if ($state->getHero()->getLife() < 20) {
            return $weight - 500;
        }*/

        return $weight;
    }

    private function weightHero(Tile\Hero $tile, array $steps, State $state)
    {
        $weight = \count($steps);

        # If another Hero is stronger than us, we don't want to meet him
        if ($state->getHero()->getLife() <
            $tile->getHero()->getLife()) {
            return $weight + 1000;
        }

        # If another Hero is weaker than us, we WANT to meet him
        if ($state->getHero()->getLife() - $tile->getHero()->getLife() > ($weight + 5)) {
            return $weight - 60;
        }

        return $weight;
    }

    private function weightTreasure(Tile\Treasure $tile, array $steps, State $state): float
    {
        $weight = \count($steps);

        # If a gold mine belongs to us, we don't need to go there
        if ($tile->getOwner() && (string) $state->getHero()->getPosition() === (string) $tile->getOwner()->getPosition()) {
            return $weight + 1000;
        }

        # If a gold mine does NOT belong to us, we want to go there
        if (($state->getHero()->getLife() > \count($steps) + 22) &&
            (!$tile->getOwner() ||
                (string) $state->getHero()->getPosition() !== (string) $tile->getOwner()->getPosition())) {
            return $weight - 60;
        }

        return $weight;
    }
}
