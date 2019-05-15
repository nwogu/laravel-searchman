<?php

namespace Nwogu\SearchMan\Breakers;

use Nwogu\SearchMan\Contracts\IndexBreaker;
use Nwogu\SearchMan\Helpers\Constants;

class CurlyBreaker extends IndexBreaker
{
    //define default possible breakers
    public static $characterBreakers = [
        "opener" => "(",
        "closer" => ")"
    ];
    
    public function break(): string
    {
        preg_match("/\\"
            . self::$characterBreakers['opener']
            . "(.*?)\\"
            . self::$characterBreakers['closer']
            . "/", $this->target, $match);

        if (empty($match)) {
            return $this->target;
        }

        $sanitized = str_replace($match[0], "", $this->target);

        $newIndex = $match[1];
        if (str_contains($newIndex, [self::$characterBreakers["opener"], self::$characterBreakers["closer"]])){
            throw new \Exception(Constants::CURLY_BRACKET_EXCEPTION);
        }
        return $newIndex . " " . $sanitized;
    }
}
