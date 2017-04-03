<?php

namespace Vindinium;

use Vindinium\HttpPost;
use Vindinium\Bot\Random;
use Vindinium\Structs\Game;
use Vindinium\BotInterface;
use Vindinium\Structs\State;
use Vindinium\Renderer\StateRenderer;

class Client
{
    CONST TIMEOUT = 15;

    /** @var string */
    private $key;

    /** @var string */
    private $mode;

    /** @var int */
    private $numberOfGames;

    /** @var int */
    private $numberOfTurns;

    /** @var string */
    private $serverUrl;

    /**
     * @param string $key
     * @param string $mode
     * @param int $numberOfGames
     * @param int $numberOfTurns
     * @param string $serverUrl
     */
    public function __construct($key, $mode, $numberOfGames, $numberOfTurns, $serverUrl)
    {
        $this->key = $key;
        $this->mode = $mode;
        $this->numberOfGames = $numberOfGames;
        $this->numberOfTurns = $numberOfTurns;
        $this->serverUrl = $serverUrl;
    }

    /**
     * @param BotInterface $bot
     * @param StateRenderer $formatter
     */
    public function run(BotInterface $bot, StateRenderer $formatter)
    {
        for ($i = 0; $i <= ($this->numberOfGames - 1); $i++) {
            $this->start($bot, $formatter);
            echo PHP_EOL . 'Game finished: '. ($i + 1) . '/' . $this->numberOfGames . PHP_EOL;
        }
    }

    /**
     * @param BotInterface $botObject
     * @param StateRenderer $formatter
     */
    private function start(BotInterface $botObject, StateRenderer $formatter)
    {
        // Starts a game with all the required parameters
        if ($this->mode === 'arena') {
            echo 'Connected and waiting for other players to join...' . PHP_EOL;
        }

        // Get the initial state
        $state = $this->getNewGameState();

        while ($this->isFinished($state) === false) {

            $formatter->renderState($state);

            // Move to some direction
            $url = $state->getPlayUrl();
            $direction = $botObject->move($state);
            $state = $this->move($url, $direction);
        }
    }

    private function getNewGameState()
    {
        $params = array('key' => $this->key);
        $api_endpoint = '/api/arena';

        // Get a JSON from the server containing the current state of the game
        if ($this->mode === 'training') {
            // Don't pass the 'map' parameter if you want a random map
            $params = ['key' => $this->key, 'turns' => $this->numberOfTurns, 'map' => 'm1'];
            $api_endpoint = '/api/training';
        }

        // Wait for 10 minutes
        $r = HttpPost::post($this->serverUrl . $api_endpoint, $params, 10 * 60);

        if (isset($r['headers']['status_code']) && $r['headers']['status_code'] === 200) {
            return State::fromJson(json_decode($r['content'], true));
        }

        echo 'Error when creating the game'. PHP_EOL;
        echo $r['content'];
    }

    /**
     * @param string $url
     * @param string $direction
     * @return State|null
     */
    private function move($url, $direction)
    {
        /*
         * Send a move to the server
         * Moves can be one of: 'Stay', 'North', 'South', 'East', 'West'
         */
        try {
            $r = HttpPost::post($url, ['dir' => $direction], self::TIMEOUT);
            if (isset($r['headers']['status_code']) && $r['headers']['status_code'] === 200) {
                return State::fromJson(json_decode($r['content'], true));
            }

            echo 'Error HTTP '. $r['headers']['status_code'] . "\n" . $r['content'] . "\n";
            return null;
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return null;
        }
    }

    /**
     * @param State $state
     * @return bool
     */
    private function isFinished(State $state)
    {
        return $state && $state->getGame()->isFinished();
    }
}