<?php

namespace Nwogu\SearchMan\Helpers;

class Indexable
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $brokenValues = [];

    /**
     * @var PriorityHandlerInterface
     */
    protected $handler;

    /**
     * Indices
     * @var array
     */
    protected $indices = [];


    public function __construct($column, $values, $handler)
    {
        $this->column = $column;

        $this->values = $this->collectValues($values);

        $this->validateHandler(new $handler);
    }

    /**
     * Get Indexable Column
     */
    public function column()
    {
        return $this->column;
    }

    /**
     * Get Indexable Values
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * Validates Priority Handler
     * @param string $priority
     * @return @var Indexable
     * @throws Exception
     */
    private function validateHandler($handler)
    {
        if ($handler instanceOf PriorityHandlerInterface) {
            $this->handler = $handler;

            return true;
        }
        throw new \Exception(Constants::HANDLER_EXCEPTION);
    }

    /**
     * Filters Column Values
     * @param array $values
     * @return array $values
     */
    private function collectValues($values)
    {
        $filter = function ($value, $filterableFunctions) {
            foreach ($filterableFunctions as $func) {
                $validities[] = $func($value);
            }
            return !in_array(true, $validities);
        };

        $is_date_time = function ($myString) {
            return \DateTime::createFromFormat('Y-m-d H:i:s', $myString) === FALSE;
        };

        $is_short_string = function ($myString) {
            return strlen($myString) < config("searchman.indexable_length", 5);
        };

        if (! is_array($values)) {
            if ($filter($values, ["is_null", $is_date_time, "is_numeric", $is_short_string])) {
                $this->values[] = $values;
            }
            return $this;
        }
        foreach (array_values($values) as $value) {
            $this->collectValues($value);
        }
    }

    /**
     * Break Values
     * @return Nwogu\Helpers\Indexable
     */
    private function breakValues()
    {
        foreach ($this->values as $value) {
            $this->brokenValues[] = $this->breakFieldUsing($value);
        }
        return $this;
    }

    /**
     * Pass Words to Breakers
     * @param string $words
     * @return string $brokenWords
     */
    private function breakFieldUsing(string $words)
    {
        $brokenWords = null;

        foreach ($this->handler->getBreakers() as $breaker) {
            $words = is_null($brokenWords) ? $words : $brokenWords;

            $breakerClass = new $breaker($words);
            if (! ($breakerClass instanceof IndexBreaker)) {
                throw new \Exception(Constants::BREAKER_EXCEPTION);
            }
            $brokenWords = $breakerClass->break();
        }
        return $brokenWords;
    }

    /**
     * Get indices of indexable
     * @return array $this->indices
     */
    public function getIndices()
    {
        $this->breakValues();
        foreach ($this->brokenValues as $broken) {
            foreach (array_filter(explode(" ", $broken)) as $index) {
                $indexLoad['index'] = $index;
                $indexLoad['priority'] = $this->handler->calculate($index, $broken);
                $this->indices[] = $indexLoad;
            }
        }
        return $this->indices;
    }
}