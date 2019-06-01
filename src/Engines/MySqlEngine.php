<?php

namespace Nwogu\SearchMan\Engines;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Nwogu\SearchMan\Helpers\Indexer;
use Nwogu\SearchMan\Helpers\Searcher;
use Nwogu\SearchMan\Helpers\Indexable;
use Nwogu\SearchMan\PriorityHandlers\LocationPriorityHandler;

class MySqlEngine extends Engine
{

    public function performUpdate($model)
    {
        $searchableColumns = $model->refresh()->toSearchableArray();

        $indexer = (new Indexer($model))->delete();

        foreach ($searchableColumns as $column => $value) {
            
            $priorityHandler = !in_array($column, array_keys($model->getIndexablePriorities())) 
                ? config('searchman.default_priority_handler', LocationPriorityHandler::class)
                : $model->getIndexablePriorities()[$column];

            $indexable = new Indexable($column, $value, $priorityHandler);

            $indexer->index($indexable);
        }
        return $model;
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['hits'])->pluck('document_id')->values();
    }

    /**
     * Delete Index
     */
    public function delete($models)
    {
        if ($models->isEmpty()) return;

        $models->map(function ($model) {
            (new Indexer($model))->delete();
        });

        return true;
    }

    /**
     * Update Index
     */
    public function update($models)
    {
        $models->map(function ($model) {
            $this->performUpdate($model);
        });
        return true;
    }

    /**
     * Search
     * @param Builder $builder
     */
    public function search(Builder $builder)
    {
        return (new Searcher($builder))->search();
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return (new Searcher($builder))->search((($page * $perPage) - $perPage));
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($results['hits']->count() === 0) {
            return $model->newCollection();
        }

        $objectIds = $results['hits']->pluck('document_id')->values()->all();

        $priorities = $results['hits']->pluck('priority', 'document_id')->toArray();

        return $model->getScoutModelsByIds(
                $builder, $objectIds
            )
            ->filter(function ($model) use ($objectIds) {
                return in_array($model->getScoutKey(), $objectIds);
            })
            ->map(function ($model) use ($priorities) {
                return $model->setAttribute('priority', $priorities[$model->id] ?? 0);
            })
            ->sortByDesc('priority')->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['total'];
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function flush($model)
    {
        return (new Indexer($model))->truncate();
    }
}