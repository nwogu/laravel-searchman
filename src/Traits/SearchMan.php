<?php

namespace Nwogu\SearchMan\Traits;

use Laravel\Scout\Searchable;

trait SearchMan
{
    use Searchable;

    /**
     * Get Indexable Priorities. Specify the priority handlers
     * for each indexable columns
     * @return array
     */
    public function getIndexablePriorities()
    {
        return [];
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix') . $this->getTable() . config('searchman.suffix');
    }

    /**
     * Get Table Columns
     */
    public function getColumns()
    {
        return array_map(function ($column) {
            return $this->qualifyColumn($column);
        } , 
        $this->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($this->getTable())
        );
    }

}