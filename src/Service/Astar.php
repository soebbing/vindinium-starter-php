<?php

namespace Vindinium\Service;

use JMGQ\AStar\Node;
use Vindinium\Parser\TileParser;
use Vindinium\Structs\Game;
use Vindinium\Structs\Board;
use Vindinium\Structs\Position;
use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Interpreted\Tile\Grass;
use Vindinium\Structs\Interpreted\Tile\Hero;
use Vindinium\Structs\Interpreted\Tile\Spawn;
use Vindinium\Structs\Interpreted\Tile\Tavern;
use Vindinium\Structs\Interpreted\Tile\Treasure;
use Vindinium\Structs\Interpreted\Tile\Wood;

class Astar extends \JMGQ\AStar\AStar
{
    /** @var Game */
    private $game;

    /** @var TileParser */
    private $tileParser;

    /** @var Tile[] */
    private $tiles;

    /**
     * @param TileParser $tileParser
     */
    public function __construct(TileParser $tileParser)
    {
        $this->tileParser = $tileParser;
    }

    /**
     * @param Game $game
     */
    public function setGame(Game $game)
    {
        $this->game = $game;

        $this->tiles = $this->tileParser->parse($game);
    }

    /**
     * @param Node $node
     * @return Node[]
     */
    public function generateAdjacentNodes(Node $node)
    {
        $adjacent = [];

        $nodeTile = $this->findTileForNode($node, $this->tiles);

        foreach ($this->tiles as $tile) {
            if ($this->areAdjacent($nodeTile, $tile)) {
                $adjacent[] = $tile;
            }
        }

        return $adjacent;
    }

    /**
     * @param Node $node
     * @param Node $adjacent
     * @return integer | float
     */
    public function calculateRealCost(Node $node, Node $adjacent)
    {
        switch (get_class($adjacent)) {
            case Grass::class:
                return 1;
                break;

            case Spawn::class:
                return 1.1;
                break;

            default:
                return PHP_INT_MAX;
        }
    }

    /**
     * @param Node $start
     * @param Node $end
     * @return integer | float
     */
    public function calculateEstimatedCost(Node $start, Node $end)
    {
        $utility = $this->manhattanDistance($start, $end);
        $endTile = $this->findTileForNode($end, $this->tiles);

        if (!$endTile->isWalkable() ||
            ($endTile->getType() === Tile::TREASURE && $endTile->getOwner() && $endTile->getOwner()->getName() === $this->game->getHero()->getName()) ||
            ($endTile->getType() === Tile::WOOD)) {
            return 10 * $utility;
        }

        if ($endTile->getType() === Tile::HERO && $endTile->getOwner() !== $this->game->getHero()) {
            /** @var \Vindinium\Structs\Hero[] $hero */
            $heros = $this->game->getHeroes();

            /** @var \Vindinium\Structs\Hero $hero */
            foreach ($heros as $hero) {
                if ($hero->getLife() - $this->game->getHero()->getLife() < 10) {
                    $utility -= 1 / $utility;
                } else {
                    $utility += 1 / $utility;
                }
            }
        }

        $adjTiles = $this->getAdjacentTiles($endTile);
        foreach ($adjTiles as $tile)
            if ($tile->getType() === Tile::TAVERN) {
                $utility -= 1 / ($utility + 1);
                break;
            }

        return $utility;
    }

    /**
     * @param Position $a
     * @param Position $b
     * @return bool
     */
    private function areAdjacent(Node $a, Node $b)
    {
        return abs($a->getX() - $b->getX()) <= 1 &&
            abs($a->getY() - $b->getY()) <= 1;
    }

    /**
     * @param Node $node
     * @param Tile[] $tiles
     * @return Tile
     * @throws \OutOfBoundsException
     */
    private function findTileForNode(Node $node, array $tiles)
    {
        list($x, $y) = explode('x', $node->getID());

        /** @var Tile $tile */
        foreach ($tiles as $tile) {
            if ($tile->getPosition()->getX() === (int) $x &&
                $tile->getPosition()->getY() === (int) $y) {
                return $tile;
            }
        }

        throw new \OutOfBoundsException('No tile for node "' . $node->getID() . '" found.');
    }

    /**
     * @param Node $start
     * @param Node $end
     * @return number
     */
    private function manhattanDistance(Node $start, Node $end)
    {
        $dx = abs($start->getX() - $end->getX());
        $dy = abs($start->getY() - $end->getY());
        return ($dx + $dy);
    }

    /**
     * @param Tile $endTile
     * @return Tile[]
     */
    private function getAdjacentTiles(Tile $endTile)
    {
        $adjacent = [];

        /** @var Tile $tile */
        foreach ($this->tiles as $tile) {
            if ($tile->getPosition()->getX() === ($endTile->getPosition()->getX() - 1) ||
                $tile->getPosition()->getX() === ($endTile->getPosition()->getX() + 1) ||
                $tile->getPosition()->getY() === ($endTile->getPosition()->getY() - 1) ||
                $tile->getPosition()->getY() === ($endTile->getPosition()->getY() - 1)) {
                $adjacent[] = $tile;
            }
        }

        return $adjacent;
    }
}