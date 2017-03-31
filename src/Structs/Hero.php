<?php

namespace Vindinium\Structs;

use Vindinium\Structs\Position;
use Vindinium\PositionableInterface;

class Hero implements PositionableInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $userId;

    /** @var int */
    private $elo;

    /** @var Position */
    private $pos;

    /** @var Board::South\Board::North\Board::West\Board::East */
    private $lastDirection;

    /** @var int */
    private $life;

    /** @var int */
    private $gold;

    /** @var int */
    private $mineCount;

    /** @var Position */
    private $spawnPos;

    /** @var bool */
    private $crashed;

    /**
     * @param array $json
     * @return Hero
     */
    public static function fromJson(array $json)
    {
        $hero = new Hero();
        foreach ($json as $key => $value) {
            if ('position' === $key) {
                $hero->$key = ('Vindinium\Structs\\' . $key)::fromJson($value);
            }

            $hero->$key = $value;
        }

        return $hero;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * @return Position
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @return Board
     */
    public function getLastDirection()
    {
        return $this->lastDirection;
    }

    /**
     * @return int
     */
    public function getLife()
    {
        return $this->life;
    }

    /**
     * @return int
     */
    public function getGold()
    {
        return $this->gold;
    }

    /**
     * @return int
     */
    public function getMineCount()
    {
        return $this->mineCount;
    }

    /**
     * @return Position
     */
    public function getSpawnPos()
    {
        return $this->spawnPos;
    }

    /**
     * @return bool
     */
    public function isCrashed()
    {
        return $this->crashed;
    }

    /**
     * @return Position
     */
    public function getPosition()
    {
        return $this->pos;
    }
}