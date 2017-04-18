<?php

namespace Vindinium\Structs;

use JMGQ\AStar\AbstractNode;
use Vindinium\PositionableInterface;

class Distance extends AbstractNode
{
    /** @var int */
    private $x;

    /** @var int */
    private $y;

    /** @var PositionableInterface */
    private $target;

    /**
     * @param int $x
     * @param int $y
     * @param PositionableInterface $target
     */
    public function __construct($x, $y, PositionableInterface $target)
    {
        $this->x = $x;
        $this->y = $y;
        $this->target = $target;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return PositionableInterface
     */
    public function getTarget()
    {
        return $this->target;
    }
}