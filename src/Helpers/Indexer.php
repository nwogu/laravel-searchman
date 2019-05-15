<?php

namespace Nwogu\SearchMan\Helpers;

use Illuminate\Support\Facades\DB;
use Nwogu\SearchMan\Helpers\Indexable;

class Indexer
{
    /**
     * Connection
     */
    protected $connection;

    /**
     * Model
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;

        $this->connection = DB::connection(config('searchman.connection'))
            ->table($this->model->searchableAs());
    }

    /**
     * Call Index
     */
    public function index(Indexable $indexable)
    {
        $this->delete();
        foreach ($indexable->getIndices() as $index) {
            $this->connection->insert([
                'keyword' => $index['index'],
                'document_id' => $this->model->getScoutKey(),
                'priority' => $index['priority'],
                'column' => $indexable->column()
            ]);
        }
        return $this;
    }

    /**
     * Remove Indices
     */
    public function delete()
    {
        $removePreviousIndexBuilder = $this->connection->where('document_id', $this->model->getScoutKey());
        if ($removePreviousIndexBuilder->exists()) {
            $removePreviousIndexBuilder->chunk(config('scout.chunk.searchable'), function ($indices) {
                foreach ($indices as $index) {
                    $index->delete();
                }
            });
        }
        return true;
    }

    /**
     * Truncate Index data
     * @param Model $model
     * @return void
     */
    public function truncate()
    {
        return $this->connection->delete();
    }
}