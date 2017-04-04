<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$input = new \Symfony\Component\Console\Input\ArgvInput();
$output = new \Symfony\Component\Console\Output\ConsoleOutput();

$app = new Pimple\Container([
    'bot' => new \Vindinium\Bot\LordHelmchen(),
    'formatter' => new Vindinium\Renderer\StateRenderer($output)
]);

$console = new Symfony\Component\Console\Application();
$console->addCommands([
    new \Vindinium\Commands\Train('train', $app),
    new \Vindinium\Commands\Fight('fight', $app)
]);
$console->run($input, $output);
