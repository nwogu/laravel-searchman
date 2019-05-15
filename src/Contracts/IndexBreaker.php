<?php

namespace Nwogu\SearchMan\Contracts;

/**
 * Helper Class to handle special characters
 * of Indexable Words
 */
abstract class IndexBreaker
{
    /**
     * Word To Break
     * @var string
     */
    protected $target;

    public function __construct($word)
    {
        $this->target = strtolower($word);
    }

    /**
     * Break Word
     * @return string $this->target
     */
    abstract public function break(): string;
}