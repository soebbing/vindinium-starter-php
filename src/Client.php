<?php

namespace Vindinium;

use Vindinium\HttpPost;
use Vindinium\Bot\Random;
use Vindinium\Structs\Game;
use Vindinium\BotInterface;
use Vindinium\Structs\State;

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
     * @param null|BotInterface $bot
     */
    public function run(BotInterface $bot = null)
    {
        if (!$bot) {
            $bot = new Random();
        }

        for ($i = 0; $i <= ($this->numberOfGames - 1); $i++) {
            $this->start($bot);
            echo PHP_EOL . 'Game finished: '. ($i + 1) . '/' . $this->numberOfGames . PHP_EOL;
        }
    }

    /**
     * @param BotInterface $botObject
     */
    private function start(BotInterface $botObject)
    {
        // Starts a game with all the required parameters
        if ($this->mode === 'arena') {
            echo 'Connected and waiting for other players to join...' . PHP_EOL;
        }

        // Get the initial state
        $state = $this->getNewGameState();

        var_dump($state);die;
        echo "Size: $size\n";
        for ($i = 0; $i < $size; $i++) {
            echo substr($tiles, $size*$i*2, $size*2) . PHP_EOL;
        }

        die;
        echo 'Playing at: '. $state['viewUrl'] . "\n";

        ob_start();
        while ($this->isFinished($state) === false) {
            // Some nice output ;)
            echo '.';
            ob_flush();

            // Move to some direction
            $url = $state['playUrl'];
            $direction = $botObject->move($state);
            $state = $this->move($url, $direction);
        }
        ob_end_clean();
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
     * @return array|null
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
                return json_decode($r['content'], true);
            }

            echo 'Error HTTP '. $r['headers']['status_code'] . "\n" . $r['content'] . "\n";
            return array('game' => array('finished' => true));
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return array('game' => ['finished' => true]);
        }
    }

    private function isFinished($state)
    {
        return $state['game']['finished'];
    }
}