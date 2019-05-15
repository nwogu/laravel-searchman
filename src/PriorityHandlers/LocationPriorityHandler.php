<?php

namespace Nwogu\SearchMan\PriorityHandlers;

use Nwogu\SearchMan\Breakers\UnitBreaker;
use Nwogu\SearchMan\Breakers\CurlyBreaker;
use Nwogu\SearchMan\Contracts\PriorityHandler;
use Nwogu\SearchMan\Breakers\SpecialCharacterBreaker;

class LocationPriorityHandler extends PriorityHandler
{
    public function calculate(string $index, $columnValue)
    {
        $values = array_flatten(array_filter(explode(" ", $columnValue)));
        return array_search($index, $values) / sizeOf($values);
    }

    public function getBreakers(): array
    {
        return [
            CurlyBreaker::class,
            UnitBreaker::class,
            SpecialCharacterBreaker::class
        ];
    }
}