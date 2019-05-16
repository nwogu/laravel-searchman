<?php

namespace Nwogu\SearchMan\PriorityHandlers;

use Nwogu\SearchMan\Contracts\PriorityHandler;
use Nwogu\SearchMan\Breakers\SpecialCharacterBreaker;

class LongTextPriorityHandler implements PriorityHandler
{

    public function calculate(string $index, $columnValue)
    {
        $doubleWordCount = sizeof(array_filter(explode(" ",$columnValue))) * 2;

        $frequency = array_count_values(str_word_count($columnValue, 1))[$index] ?? 0;

        return $frequency / $doubleWordCount ;
    }

    public function getBreakers():array
    {
        return [ SpecialCharacterBreaker::class ];
    }
}