<?php

namespace Vindinium\Bot;

use JMGQ\AStar\Node;
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

    /**
     * @return array
     */
    public function getRoute(): array
    {
        return $this->route;
    }

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
        $this->astar->setState($state);
        $tiles = $this->tileParser->parse($state);

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
            $steps = $this->astar->run($this->getTileForPosition($state->getHero()->getPosition(), $tiles), $tile);

            $targets[] = [
                'weight' => count($steps), # lower is better
                'tile' => $tile,
                'steps' => $steps,
            ];
        }

        foreach ($targets as &$distance) {
            /** @var Tile $tile */
            $tile = $distance['tile'];

            # If another Hero is stronger than us, we don't want to meet him
            if ($tile->getType() === Tile::HERO &&
                $state->getHero()->getLife() <
                $tile->getHero()->getLife()) {
                $distance['weight'] += 1000;
            }

            # If a gold mine belongs to us, we don't need to go there
            if ($tile->getType() === Tile::TREASURE &&
                ($tile->getOwner() && $state->getHero()->getName() === $tile->getOwner()->getName())) {
                $distance['weight'] += 1000;
            }

            # If a gold mine does NOT belong to us, we want to go there
            if ($tile->getType() === Tile::TREASURE &&
                (!$tile->getOwner() ||
                $state->getHero()->getName() !== $tile->getOwner()->getName())) {
                $distance['weight'] -= 50;
            }

            # If we have enough life, we don't want to go to a tavern
            if ($tile->getType() === Tile::TAVERN &&
                $state->getHero()->getLife() > 25) {
                $distance['weight'] += 100;
            }

            # If we DON'T have enough life, we don't want to go to a tavern
            if ($tile->getType() === Tile::TAVERN &&
                $state->getHero()->getLife() < 20) {
                $distance['weight'] -= 500;
            }
        }
        unset($distance);

        // Sort the list by weights, asc
        usort($targets, function($a, $b) {
            return $a['weight'] > $b['weight'] ? 1 : -1;
        });

        $target = array_shift($targets);


        return $target['steps'];
    }

    /**
     * Find all relevant target nodes
     *
     * @param State $state
     * @param array $tiles
     * @return Tile[]
     */
    private function buildTargetList(State $state, array $tiles): array
    {
        $otherHeros = $this->getHeros($state->getHero(), $state->getGame()->getHeroes(), $tiles);
        $treasures = $this->getTreasures($state->getHero(), $tiles);
        $taverns = $this->getTaverns($tiles);

        return array_merge($otherHeros, $treasures, $taverns);
    }

    /**
     * @param Hero $hero
     * @param Tile[] $tiles
     * @return Tile[]
     */
    private function getTreasures(Hero $hero, array $tiles): array
    {
        $treasures = [];

        foreach ($tiles as $tile) {
            if ($tile->getType() === Tile::TREASURE &&
                (!$tile->getOwner() || ($tile->getOwner()->getName() !== $hero->getName()))) {
                $treasures[] = $tile;
            }
        }

        return $treasures;
    }

    /**
     * @param Tile[] $tiles
     * @return Tile[]
     */
    private function getTaverns(array $tiles): array
    {
        $taverns = [];

        foreach ($tiles as $tile) {
            if ($tile->getType() === Tile::TAVERN) {
                $taverns[] = $tile;
            }
        }

        return $taverns;
    }

    /**
     * @param Hero $lordHelmchen
     * @param Hero[] $allHeros
     * @param Tile[] $tiles
     *
     * @return Tile[]
     */
    private function getHeros(Hero $lordHelmchen, array $allHeros, array $tiles): array
    {
        $heros = [];

        foreach ($tiles as $tile) {
            /** @var Hero $hero */
            foreach ($allHeros as $hero) {
                if ($hero->getName() !== $lordHelmchen->getName() && $hero->getPosition()->getID() === $tile->getPosition()->getID()) {
                    $heros[] = $tile;
                }
            }
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
            return  Board::West;
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
     * @param Tile[] $tiles
     *
     * @throws \OutOfBoundsException
     *
     * @return Tile
     */
    private function getTileForPosition(Position $position, array $tiles)
    {
        foreach ($tiles as $tile) {
            if ($tile->getPosition()->getID() === $position->getID()) {
                return $tile;
            }
        }

        throw new \OutOfBoundsException('Tile for Position ' . $position->getID() . ' not found');
    }
}
