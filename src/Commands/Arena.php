<?php

namespace Vindinium\Commands;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Arena extends Command
{
    /**
     * @var Container
     */
    private $dic;

    /**
     * @param null|string $name
     * @param Container $dic
     * @internal param BotInterface $bot
     */
    public function __construct($name, Container $dic)
    {
        $this->dic = $dic;
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName('arena')
            ->setDescription('Fights with your bot in the arena')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command Sends your bot to the arena to fight against other bots.
EOF
            )
            ->addArgument('token', InputOption::VALUE_REQUIRED, 'The token of your bot to identify against the server')
            ->addOption('numberOfTurns', 't', InputOption::VALUE_OPTIONAL, 'Number of turns to run', 300)
            ->addOption('host', 'ho', InputOption::VALUE_OPTIONAL, 'Arena to fight in', 'http://vindinium.org');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $input->getArgument('token');

        if (!$token) {
            $output->writeln('Missing argument token');
            return false;
        }

        $numberOfGames = 1;
        $numberOfTurns = (int) $input->getOption('numberOfTurns');

        $client = new \Vindinium\Client($token, 'arena', $numberOfGames, $numberOfTurns, $input->getOption('host'));
        $client->run($this->dic->offsetGet('bot'), $this->dic->offsetGet('formatter'));
    }
}