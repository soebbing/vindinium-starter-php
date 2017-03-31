<?php

require __DIR__ . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['argc'] < 4) {
    echo 'Usage: '. $_SERVER['SCRIPT_FILENAME'] . " <key> <[training|arena]> <number-of-games|number-of-turns> [server-url]\n";
    echo 'Example: '. $_SERVER['SCRIPT_FILENAME'] . " mySecretKey training 20\n";
    exit (1);
}

list($script, $key, $mode, $numberOfGamesOrTurns, $serverUrl) =  $_SERVER['argv'] + [null, null, null, null, null];

if ($mode === 'training') {
    $numberOfGames = 1;
    $numberOfTurns = (int) $numberOfGamesOrTurns;
} else {
    $numberOfGames = (int) $numberOfGamesOrTurns;
    $numberOfTurns = 300; # Ignored in arena mode
}

if (!$serverUrl) {
    $serverUrl = 'http://vindinium.org';
}

$client = new \Vindinium\Client($key, $mode, $numberOfGames, $numberOfTurns, $serverUrl);
$client->run();