<?php

namespace Vindinium\Bot;

use JMGQ\AStar\Node;
use Vindinium\BotInterface;
use Vindinium\Parser\TileParser;
use Vindinium\Service\Astar;
use Vindinium\Structs\Board;
use Vindinium\Structs\Game;
use Vindinium\Structs\Hero;
use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Position;
use Vindinium\Structs\State;

class LordHelmchen implements BotInterface
{
    /** @var Astar */
    private $astar;

    /** @var TileParser */
    private $tileParser;

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
     * @return mixed
     */
    public function move(State $state)
    {
        $this->astar->setGame($state->getGame());

        $tiles = $this->tileParser->parse($state->getGame());

        $targetNode = $this->findTargetNode($state, $tiles);
var_dump($targetNode);
        $direction = $this->getDirectionToNode($state->getHero()->getPosition(), $targetNode, $tiles);

        return $direction;
    }

    /**
     * @param State $state
     * @param Tile[] $tiles
     * @return Tile
     */
    private function findTargetNode(State $state, array $tiles)
    {
        $targetTiles = $this->buildLinkList($state, $tiles);

        $distances = [];

        $targetTiles = array_slice($targetTiles,0,5);

        foreach ($targetTiles as $tile) {
            $steps = $this->astar->run($this->getTileForPosition($state->getHero()->getPosition(), $tiles), $tile);
            $distances[] = [
                count($steps),
                $tile,
                $steps
            ];
        }

        usort($distances, function($a, $b) {
            return $a[0] < $b[0] ? 1 : -1;
        });

        return array_pop($distances)[1];
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
                var_dump($tile->getOwner());
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
     * @param Position $currentNode
     * @param Node|Tile $targetNode
     * @param Tile[] $tiles
     * @return string
     */
    private function getDirectionToNode(Position $currentNode, Tile $targetNode, array $tiles)
    {
        $direction = Board::Stay;

        if ($currentNode->getX() > $targetNode->getX() &&
            $this->getTileForPosition(new Position($currentNode->getX() - 1, $currentNode->getY()), $tiles)->isWalkable()) {
            $direction = Board::North;
        }

        if ($currentNode->getX() < $targetNode->getX() &&
            $this->getTileForPosition(new Position($currentNode->getX() + 1, $currentNode->getY()), $tiles)->isWalkable()) {
            $direction = Board::South;
        }

        if ($currentNode->getY() > $targetNode->getY() &&
            $this->getTileForPosition(new Position($currentNode->getX(), $currentNode->getY() - 1), $tiles)->isWalkable()) {
            $direction = Board::West;
        }

        if ($currentNode->getY() < $targetNode->getY() &&
            $this->getTileForPosition(new Position($currentNode->getX(), $currentNode->getY() + 1), $tiles)->isWalkable()) {
            $direction = Board::East;
        }

        return $direction;
    }

    /**
     * Return the Tile object for a given Position
     *
     * @param Position $position
     * @param Tile[] $tiles
     * @return Tile
     */
    private function getTileForPosition(Position $position, array $tiles)
    {
        foreach ($tiles as $tile) {
            if ($tile->getPosition()->getID() === $position->getID()) {
                return $tile;
            }
        }

        throw new \OutOfBoundsException('Tile for Position ' . $position->getID() . ' obnt found');
    }
}