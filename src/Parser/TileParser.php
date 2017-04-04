<?php

namespace Vindinium\Parser;

use Vindinium\Structs\Game;
use Vindinium\Structs\Position;
use Vindinium\Structs\Interpreted\Tile\Wood;
use Vindinium\Structs\Interpreted\Tile\Grass;
use Vindinium\Structs\Interpreted\Tile\Hero;
use Vindinium\Structs\Interpreted\Tile\Tavern;
use Vindinium\Structs\Interpreted\Tile\Treasure;

class TileParser
{
    public function parse(Game $game)
    {
        $tiles = $game->getBoard()->getTiles();
        $tileStructs = [];
        for ($row = 0, $size = $game->getBoard()->getSize(); $row < $size; $row++) {

            for ($tile = 0; $tile < ($size * 2); $tile += 2) {
                switch (true) {
                    case substr($tiles, ($row * $size * 2) + $tile, 2) === '##':
                        $tileStructs[] = new Wood(new Position($row, $tile/2));
                        break;

                    case substr($tiles, ($row * $size * 2) + $tile, 2) === '  ':
                        $tileStructs[] = new Grass(new Position($row, $tile/2));
                        break;

                    case substr($tiles, ($row * $size * 2) + $tile, 2) === '[]':
                        $tileStructs[] = new Tavern(new Position($row, $tile/2));
                        break;

                    case $tiles[($row * $size * 2) + $tile] === '$':
                        if ($tiles[(($row * $size * 2) + $tile) + 1] === '-') {
                            $tileStructs[] = new Treasure(new Position($row, $tile/2), null);
                            break;
                        }

                        $number = $tiles[(($row * $size * 2) + $tile) + 1];
                        $tileStructs[] = new Treasure(new Position($row, $tile/2), $game->getHeroes()[$number-1]);
                        break;

                    case $tiles[($row * $size * 2) + $tile] === '@':
                        $number = $tiles[(($row * $size * 2) + $tile) + 1];
                        $tileStructs[] = new Hero(new Position($row, $tile/2), $game->getHeroes()[$number-1]);
                        break;
                }
            }
        }

        return $tileStructs;
    }
}