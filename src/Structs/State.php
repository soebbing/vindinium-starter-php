<?php

namespace Vindinium\Structs;

/**
 * Describes the state of the game, the board incl. everything and everyone on it.
 */
class State
{
    /** @var string */
    private $token;

    /** @var string */
    private $viewUrl;

    /** @var string */
    private $playUrl;

    /**
     * @param array $json
     * @return State
     */
    public static function fromJson(array $json)
    {
        $state = new State();
        foreach ($json as $key => $value) {
            if (in_array($key, ['game', 'hero'], true)) {
                $state->$key = 'Vindinium\Structs\\' . ucfirst($key)::fromJson($value);
            }

            $state->$key = $value;
        }

        return $state;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return $this->viewUrl;
    }

    /**
     * @return string
     */
    public function getPlayUrl()
    {
        return $this->playUrl;
    }

}