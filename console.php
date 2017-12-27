<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$input = new \Symfony\Component\Console\Input\ArgvInput();
$output = new \Symfony\Component\Console\Output\ConsoleOutput();

$astar = new \Vindinium\Service\Astar();

$app = new Pimple\Container([
    'astar' => $astar,
    'bot' => new \Vindinium\Bot\LordHelmchen($astar, new \Vindinium\Parser\TileParser()),
    'formatter' => new Vindinium\Renderer\StateRenderer($output)
]);

$console = new Symfony\Component\Console\Application();
$console->addCommands([
    new \Vindinium\Commands\Train('train', $app),
    new \Vindinium\Commands\Arena('arena', $app)
]);
$console->run($input, $output);
