<?php

namespace Vindinium\Service;

use JMGQ\AStar\Node;
use Vindinium\Parser\TileParser;
use Vindinium\Structs\State;
use Vindinium\Structs\Position;
use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Interpreted\Tile\Grass;
use Vindinium\Structs\Interpreted\Tile\Spawn;

class Astar extends \JMGQ\AStar\AStar
{
    /** @var State */
    private $state;

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
     * @param State $state
     */
    public function setState(State $state): void
    {
        $this->state = $state;

        $this->tiles = $this->tileParser->parse($state);
    }

    /**
     * @param Node $node
     *
     * @throws \OutOfBoundsException
     *
     * @return Node[]
     */
    public function generateAdjacentNodes(Node $node): array
    {
        $adjacent = [];

        $nodeTile = $this->findTileForNode($node, $this->tiles);

        foreach ($this->tiles as $tile) {
            if ($this->areAdjacent($nodeTile, $tile)) {
                #echo $nodeTile->getID() . ' && ' . $tile->getID() . " are adajecent\n";
                $adjacent[] = $tile;
            }
        }

        return $adjacent;
    }

    /**
     * @param Node $node
     * @param Node $adjacent
     * @return float
     */
    public function calculateRealCost(Node $node, Node $adjacent): float
    {
        switch (get_class($adjacent)) {
            case Grass::class:
                return 1.;
                break;

            case Spawn::class:
                return 1.1;
                break;

            default:
                return (float) PHP_INT_MAX;
        }
    }

    /**
     * @param Node $start
     * @param Node $end
     *
     * @throws \OutOfBoundsException
     *
     * @return float
     */
    public function calculateEstimatedCost(Node $start, Node $end): float
    {
        $utility = $this->manhattanDistance($start, $end);
        $endTile = $this->findTileForNode($end, $this->tiles);

        if (!$endTile->isWalkable()) {
            return $utility * 100;
        }

        if ($utility === 0) {
            $utility = 0.1;
        }

        if (($endTile->getType() === Tile::WOOD) ||
            ($endTile->getType() === Tile::TAVERN && $this->state->getHero()->getLife() > 30) ||
            ($endTile->getType() === Tile::TREASURE && $endTile->getOwner() && $endTile->getOwner() === $this->state->getHero())) {
            return $utility * 100;
        }

        if ($endTile->getType() === Tile::HERO && $endTile->getHero() !== $this->state->getHero()) {
            if (($endTile->getHero()->getLife() - $this->state->getHero()->getLife()) < 10) {
                $utility -= 1 / $utility;
            } else {
                $utility += 1 / $utility;
            }
        }

        return $utility;
    }

    /**
     * @param Node|Position $a
     * @param Node|Position $b
     * @return bool
     */
    private function areAdjacent(Node $a, Node $b): bool
    {
        $deltaX = abs($a->getX() - $b->getX());
        $deltaY = abs($a->getY() - $b->getY());
#echo "DeltaX $deltaX - DeltaY $deltaY\n";
        return ($deltaX === 1 && $deltaY === 0) ||
            ($deltaX === 0 && $deltaY === 1);
    }

    /**
     * @param Node $node
     * @param Tile[] $tiles
     *
     * @throws \OutOfBoundsException
     *
     * @return Tile
     */
    private function findTileForNode(Node $node, array $tiles): Tile
    {
        [$x, $y] = explode('x', $node->getID());

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
     * @return float
     */
    private function manhattanDistance(Node $start, Node $end): float
    {
        $dx = abs($start->getX() - $end->getX());
        $dy = abs($start->getY() - $end->getY());
        return ($dx + $dy);
    }
}
