<?php

namespace Vindinium\Renderer;

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

        $this->output->writeln("\033\143Round {$state->getGame()->getTurn()}/{$state->getGame()->getMaxTurns()}\n");

        $this->renderBoard($state);
        $this->renderStats($state);
    }

    /**
     * @param State $state
     */
    private function renderBoard(State $state)
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
                        $rowString .= '<grass>  </grass>';
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
    }

    public function setStyles()
    {
        $formatter = $this->output->getFormatter();
        $formatter->setStyle('hero1', new OutputFormatterStyle('red', 'default'));
        $formatter->setStyle('hero2', new OutputFormatterStyle('blue', 'default'));
        $formatter->setStyle('hero3', new OutputFormatterStyle('green', 'default'));
        $formatter->setStyle('hero4', new OutputFormatterStyle('yellow', 'default'));
        $formatter->setStyle('gold', new OutputFormatterStyle('cyan', 'default'));
        $formatter->setStyle('tavern', new OutputFormatterStyle('magenta', 'default'));
        $formatter->setStyle('wood', new OutputFormatterStyle('black', 'default'));
        $formatter->setStyle('grass', new OutputFormatterStyle('default', 'default'));
    }

    private function renderStats(State $state)
    {
        $hero = $state->getHero();
        $heros = [$hero];
        $number = 1;

        foreach ($state->getGame()->getHeroes() as $otherHero) {
            $this->output->writeln("<hero{$number}>{$otherHero->getName()} - Life: {$otherHero->getLife()}, Gold: {$otherHero->getGold()}, Elo: {$otherHero->getElo()}</hero{$number}>");
            $number++;
        }
    }
}