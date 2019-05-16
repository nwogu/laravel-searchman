<?php

namespace Nwogu\SearchMan\Helpers;

use Laravel\Scout\Builder;
use Illuminate\Support\Facades\DB;

class Searcher
{
    /**
     * Filters
     * @var array
     */
    protected $filters = [];

    /**
     * Builder
     * @var Builder
     */
    protected $builder;

    /**
     * Broken Query Strings
     * @var array
     */
    protected $queries;

    /**
     * Connection
     * @var DatabaseManager
     */
    protected $connection;

    /**
     * Searchable
     * @var Model
     */
    protected $model;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;

        $this->queries = array_filter(explode(" ", $this->builder->query));

        $this->model = $this->builder->model;

        $this->filters = $this->builder->wheres;

        $index = $this->builder->index ?: $this->model->searchableAs();

        $this->connection = DB::connection(config('searchman.connection'))
            ->table($index);
    }

    public function search($offset = null)
    {
        if ($this->builder->callback) {
            return call_user_func(
                $this->builder->callback,
                $this,
                $this->builder->query,
                $this->filters
            );
        }

        $searchTable = substr($this->model->getScoutKeyName(), 0, strpos($this->model->getScoutKeyName(), "."));

        $this->connection->leftJoin(
            $searchTable, "{$this->model->searchableAs()}.document_id", 
            "=", "{$this->model->getScoutKeyName()}");

        $query = implode("," , $this->model->getColumns());

        $query .= ", sum(priority) as priority, document_id";

        $this->connection->selectRaw($query)
            ->groupBy("document_id");

        $this->connection->where( function ($query) {
            foreach ($this->queries as $searchTerm) {
                $query->orWhere('keyword', "like", "%{$searchTerm}%");
            }
        });

        $this->connection->where( function ($query) {
            foreach ($this->filters as $where => $value) {

                $column = $where;
                $action = "=";

                if (strpos($where, ":")) {
                    $where = explode(":", $where);
                    $column = $where[0];
                    $action = $where[1];
                }
                $column = $this->model->qualifyColumn($column);

                $query->where($column, $action, $value);
            }
        });

        if ($this->builder->limit) {
            $this->connection->limit($this->builder->limit);
        }

        if ($offset) {
            $this->connection->offset($offset);
        }

        foreach ($this->builder->orders as $order) {
            $column = $this->model->qualifyColumn($order['column']);
            $this->connection->orderBy($column, $order['direction']);
        }

        $this->connection->orderBy("priority", "desc");

        return [
            "hits" => $this->connection->get(),
            "total" => $this->connection->get()->count(),
        ];
        
    }
}