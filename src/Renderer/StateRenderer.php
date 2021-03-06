<?php

namespace Vindinium\Renderer;

use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Position;
use Vindinium\Structs\State;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class StateRenderer
{
    /** @var OutputInterface */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln($messages, $options = 0)
    {
        $this->output->writeln($messages, $options);
    }

    /**
     * @param State $state
     */
    public function renderState(State $state)
    {
        $this->setStyles();

        $this->output->writeln("\033\143Web: {$state->getViewUrl()}\n");
        $this->output->writeln("Round {$state->getGame()->getTurn()}/{$state->getGame()->getMaxTurns()}\n");

        $this->renderBoard($state);
        $this->renderStats($state);
    }

    /**
     * @param State $state
     */
    private function renderBoard(State $state): void
    {
        $tiles = $state->getGame()->getBoard()->getTiles();
        for ($row = 0, $size = $state->getGame()->getBoard()->getSize(); $row < $size; $row++) {
            $rowString = '';

            for ($tile = 0; $tile < ($size * 2); $tile += 2) {
                switch (true) {
                    case substr($tiles, ($row * $size * 2) + $tile, 2) === '##':
                        $rowString .= '<wood>##</wood>';
                        break;

                    case substr($tiles, ($row * $size * 2) + $tile, 2) === '  ':
                        if ($this->isInPath(new Position($row, $tile/2), $state->getRoute())) {
                            $rowString .= '<route>o°</route>';
                        } else {
                            $rowString .= '<grass>  </grass>';
                        }
                        break;

                    case substr($tiles, ($row * $size * 2) + $tile, 2) === '[]':
                        $rowString .= '<tavern>[]</tavern>';
                        break;

                    case $tiles[($row * $size * 2) + $tile] === '$':
                        if ($tiles[(($row * $size * 2) + $tile) + 1] === '-') {
                            $rowString .= '<gold>$-</gold>';
                            break;
                        }

                        $number = $tiles[(($row * $size * 2) + $tile) + 1];
                        $rowString .= "<hero{$number}>$$number</hero{$number}>";
                        break;

                    case $tiles[($row * $size * 2) + $tile] === '@':
                        $number = $tiles[(($row * $size * 2) + $tile) + 1];
                        $rowString .= "<hero{$number}>@$number</hero{$number}>";
                        break;
                }
            }

            $this->output->writeln($rowString);
        }

        $this->output->writeln("\n\n");
        if ($state->getRoute()) {
            $route = $state->getRoute();
            $tile = array_pop($route);
            $this->output->writeln(sprintf('Ziel ist %s in %s', $tile->getType(), $tile->getPosition()));
        }
    }

    public function setStyles(): void
    {
        $formatter = $this->output->getFormatter();
        $formatter->setStyle('hero1', new OutputFormatterStyle('red'));
        $formatter->setStyle('hero2', new OutputFormatterStyle('blue'));
        $formatter->setStyle('hero3', new OutputFormatterStyle('green'));
        $formatter->setStyle('hero4', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('gold', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('tavern', new OutputFormatterStyle('magenta'));
        $formatter->setStyle('wood', new OutputFormatterStyle('black'));
        $formatter->setStyle('grass', new OutputFormatterStyle('default'));
        $formatter->setStyle('route', new OutputFormatterStyle(null, 'white'));
    }

    private function renderStats(State $state): void
    {
        $number = 1;

        foreach ($state->getGame()->getHeroes() as $otherHero) {
            $this->output->writeln("<hero{$number}>{$otherHero->getName()} - Life: {$otherHero->getLife()}, Gold: {$otherHero->getGold()}, Elo: {$otherHero->getElo()}</hero{$number}>");
            $number++;
        }
    }

    /**
     * @param Position $position
     * @param array $route
     * @return bool
     */
    private function isInPath(Position $position, ?array $route): bool
    {
        if (!$route) {
            return false;
        }

        /** @var Position $element */
        foreach ($route as $element) {

#            echo $position->getID() . ' == ' . $element->getPosition()->getID() . "\n";
            if ($element->getPosition()->getID() === $position->getID()) {
                return true;
            }
        }

        return false;
    }
}