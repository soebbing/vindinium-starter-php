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
    public function move(State $state)
    {
        $this->astar->setState($state);
        $tiles = $this->tileParser->parse($state);

        $targetNode = $this->findTargetNode($state, $tiles);

        $this->lastDirection = $this->getDirectionToNode($state->getHero(), $targetNode, $tiles);

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
     * @return Tile
     */
    private function findTargetNode(State $state, array $tiles)
    {
        $targetTiles = $this->buildLinkList($state, $tiles);

        $distances = [];

        foreach ($targetTiles as $tile) {
            # If a gold mine belongs to us, we don't need to go there
            if ($tile->getType() === Tile::TREASURE &&
                $tile->getOwner()) {

                continue;
            }

            $steps = $this->astar->run($this->getTileForPosition($state->getHero()->getPosition(), $tiles), $tile);
            $distances[] = [
                'weight' => count($steps), # lower is better
                'tile' => $tile,
                'steps' => $steps,
            ];
        }

        foreach ($distances as &$distance) {
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
                $state->getHero()->getLife() > 20) {
                $distance['weight'] += 100;
            }

            # If we DONT have enough life, we don't want to go to a tavern
            if ($tile->getType() === Tile::TAVERN &&
                $state->getHero()->getLife() < 20) {
                $distance['weight'] -= 500;
            }
        }

        usort($distances, function($a, $b) {
            return $a['weight'] < $b['weight'] ? 1 : -1;
        });

        $target = array_pop($distances);
        /** @var Tile $tile */
        $tile =  $target['tile'];

        #echo "Gehe zu " .  $tile->getType() . " in " . $tile->getPosition() . " weil " . $target['weight'] . "\n";
        return $tile;
    }

    /**
     * Find all relevant target nodes
     *
     * @param State $state
     * @param array $tiles
     * @return Tile[]
     */
    private function buildLinkList(State $state, array $tiles)
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
    private function getTreasures(Hero $hero, array $tiles)
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
    private function getTaverns(array $tiles)
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
    private function getHeros(Hero $lordHelmchen, array $allHeros, array $tiles)
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
     * @param Node|Tile $targetNode
     * @param Tile[] $tiles
     *
     * @throws \OutOfBoundsException
     *
     * @return string
     */
    private function getDirectionToNode(Hero $hero, Tile $targetNode, array $tiles)
    {
        $currentNode = $hero->getPosition();
        $direction = Board::Stay;

        $northTile = null;
        $southTile = null;
        $westTile = null;
        $eastTile = null;

        if ($currentNode->getX() > $targetNode->getX()) {
            $northTile = $this->getTileForPosition(new Position($currentNode->getX() - 1, $currentNode->getY()), $tiles);
            if ($northTile->isWalkable($hero)) {
                $direction = Board::North;
            }
        }

        if ($currentNode->getX() < $targetNode->getX()) {
            $southTile = $this->getTileForPosition(new Position($currentNode->getX() + 1, $currentNode->getY()), $tiles);

            if ($southTile->isWalkable($hero)) {
                $direction = Board::South;
            }
        }

        if ($currentNode->getY() > $targetNode->getY()) {
            $westTile = $this->getTileForPosition(new Position($currentNode->getX(), $currentNode->getY() - 1), $tiles);

            if ($westTile->isWalkable($hero)) {
                $direction = Board::West;
            }
        }

        if ($currentNode->getY() < $targetNode->getY()) {
            $eastTile = $this->getTileForPosition(new Position($currentNode->getX(), $currentNode->getY() + 1), $tiles);

            if ($eastTile->isWalkable($hero)) {
                $direction = Board::East;
            }
        }

        if ($direction === Board::Stay) {
            $direction = $this->getFallbackDirection($currentNode, $hero, $tiles);
        }

        return $direction;
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

    /**
     * @param Position $currentPosition
     * @param Hero $hero
     * @param Tile[] $tiles
     *
     * @return string
     */
    private function getFallbackDirection(Position $currentPosition, Hero $hero, array $tiles)
    {
        $direction = Board::Stay;

        /** @var Tile $tile */
        foreach ($tiles as $tile) {
            // South
            if ($tile->getPosition()->getX() === ($currentPosition->getX() - 1) &&
                $tile->isWalkable($hero) && in_array(
                    $this->lastDirection,
                    [Board::East, Board::West],
                    true
                )) {
                $direction = Board::South;
            }
            // North
            if ($tile->getPosition()->getX() === ($currentPosition->getX() + 1) &&
                $tile->isWalkable($hero) && in_array(
                    $this->lastDirection,
                    [Board::West, Board::East],
                    true
                )) {
                $direction = Board::North;
            }
            // West
            if ($tile->getPosition()->getY() === ($currentPosition->getY() - 1) &&
                $tile->isWalkable($hero) && in_array(
                    $this->lastDirection,
                    [Board::North, Board::South],
                    true
                )) {
                $direction = Board::West;
            }
            // East
            if ($tile->getPosition()->getY() === ($currentPosition->getY() + 1) &&
                $tile->isWalkable($hero) && in_array(
                    $this->lastDirection,
                    [Board::South, Board::North],
                    true
                )) {
                $direction = Board::East;
            }
        }

        return $direction;
    }
}
