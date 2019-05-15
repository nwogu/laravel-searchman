<?php

namespace Nwogu\SearchMan\Breakers;

use Nwogu\SearchMan\Contracts\IndexBreaker;

class SpecialCharacterBreaker extends IndexBreaker
{
    //define default possible breakers
    public static $characterBreakers = [
        "opener" => "/[^A-Za-z0-9\ ]/"
    ];
    
    public function break(): string
    {
        return preg_replace(self::$characterBreakers['opener'], '', $this->target);
    }
}
