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
        #echo "Targetnode: {$targetNode}\n";
        $direction = $this->getDirectionToNode($state->getHero()->getPosition(), $targetNode, $tiles);

        return $direction;
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
            #echo $tile->getType() . "\n";
            if ($tile->getType() === Tile::TREASURE &&
                $tile->getOwner()) {
                #echo "Unsere Goldmine - UNINTERESSANT!\n";

                continue;
            }

            $steps = $this->astar->run($this->getTileForPosition($state->getHero()->getPosition(), $tiles), $tile);

            $distances[] = [
                'weight' => count($steps), # lower is better
                'tile' => $tile,
                'steps' => $steps,
            ];
        }

        foreach ($distances as $distance) {
            # If another Hero is stronger than us, we don't want to meet him
            if ($distance['tile'] === Tile::HERO &&
                $state->getHero()->getLife() < $distance['tile']->getLife()) {
                $distance['weight'] -= 1000;
            }

            # If a gold mine belongs to us, we don't need to go there
            if ($distance['tile'] === Tile::TREASURE &&
                $state->getHero() === $distance['tile']->getOwner()) {
                $distance['weight'] -= 1000;
            }

            # If a gold mine does NOT belong to us, we want to go there
            if ($distance['tile'] === Tile::TREASURE &&
                $state->getHero()->getLife() !== $distance['tile']->getOwner()) {
                $distance['weight'] += 50;
            }

            # If we have enough life, we don't want to go to a tavern
            if ($distance['tile'] === Tile::TAVERN &&
                $state->getHero()->getLife() > 20) {
                $distance['weight'] -= 100;
            }

            # If we DONT have enough life, we don't want to go to a tavern
            if ($distance['tile'] === Tile::TAVERN &&
                $state->getHero()->getLife() < 20) {
                $distance['weight'] += 500;
            }
        }

        usort($distances, function($a, $b) {
            return $a['weight'] < $b['weight'] ? 1 : -1;
        });

        return array_pop($distances)['tile'];
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

        $taverns = [];

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
     * @param Position $currentNode
     * @param Node|Tile $targetNode
     * @param Tile[] $tiles
     *
     * @throws \OutOfBoundsException
     *
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
