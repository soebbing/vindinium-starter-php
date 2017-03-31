<?php

namespace Vindinium\Structs;

use Vindinium\Structs\Game;

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

        var_dump($json);

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