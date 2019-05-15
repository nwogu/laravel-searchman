<?php

namespace Nwogu\SearchMan\Breakers;

use Nwogu\SearchMan\Contracts\IndexBreaker;

class UnitBreaker extends IndexBreaker
{
    //define default possible breakers
    public static $characterBreakers = [
        "opener" => "/(([0-9]*\.?[0-9]+).[\s]?(\w+)+[\s]?([x,X][0-9]+)?)/"
    ];
    
    public function break(): string
    {
        preg_match(self::$characterBreakers['opener'], $this->target, $match);

        if (empty($match)) {

            return $this->target;
        }
        $sanitized = str_replace($match[0], "", $this->target);
        
        return $match[0] . " " . $sanitized;
    }
}
