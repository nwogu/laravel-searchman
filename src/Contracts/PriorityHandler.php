<?php

namespace Nwogu\SearchMan\Contracts;

interface PriorityHandler
{

    /**
     * Calculates priority for single key words
     *
     * @param string $index
     * @param string $columnValue
     * 
     * @return int?float
     */
    public function calculate(string $index, $columnValue);
    
    /**
     * Return Breakers for the indexable words
     * @return array[IndexBreaker]
     */
    public function getBreakers(): array;
}