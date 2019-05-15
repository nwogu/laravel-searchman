<?php

namespace Nwogu\SearchMan\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;

class MakeIndex extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'searchman:make-index {searchable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the searchable index table';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new searchable index table command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer    $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $searchable = new $this->argument('model');

        $tableName = $searchable->searchableAs();
        
        $fullPath = $this->createBaseMigration($tableName);

        $replaceables = [
            "{ClassName}" => $this->getSearchableModelIndexClass(),
            "{table_name}" => $tableName
        ];

        foreach ($replaceables as $search => $replace) {
            $this->files->put($fullPath, 
                str_replace($search, $replace, 
                $this->files->get(__DIR__.'/stubs/searchable_index_migration.stub')
            ));
        }


        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the indexes.
     *
     * @return string
     */
    protected function createBaseMigration($table)
    {
        $name = "create_{$table}_table";

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

    /**
     * Gets Searchable Model Index Class
     */
    protected function getSearchableModelIndexClass()
    {
        $searchable = explode("\\", $this->argument('searchable'));
        return $searchable[count($searchable)-1] . "Index";
    }
}