# laravel-searchman
MySql Driver for Laravel Scout

## Requirements  
* Requires Laravel Installed ^5.6  
* Requires Laravel Scout ^7.0  

## Installation  
```composer require nwogu\laravel-searchman```  

## Setup  
Searchman Provides a Mysql Driver for Laravel Scout, for full text search  
with indexing priorities and sql where expressions. 

[Laravel Scout Documentation](https://laravel.com/docs/5.8/scout)  

After installing Searchman, you can publish the configuration  
using the vendor:publish Artisan command. This command will publish the searchman.php  
configuration file to your config directory:  

```php artisan vendor:publish --provider="Nwogu\SearchMan\Provider\SearchableServiceProvider"```  

Add the ```Nwogu\SearchMan\Traits\SearchMan``` trait to the model you would like to make searchable. 
This trait uses Laravel's Scout Searchable and adds extra methods required by the engine:  

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nwogu\SearchMan\Traits\SearchMan;

class Meeting extends Model
{
    use SearchMan;
}

```  

As with Scout, you can overide the ```searchableAs``` method in your model to change  
the default index table name.  

### Queuing  
As at v1.0.1, Searchman has not been effectively tested with queues.  

### Migrations
Run the command ```php artisan searchman:make-index {Model}``` to generate the index  
for the specific model.  

```php artisan searchman:make-index "App\Meeting"```  

A migration file will be created in laravel's default base migrations folder.

run the migration with ```php artisan migrate``` to publish the migration.

At the point, you can now start indexing your models.

[Laravel Scout Documentation](https://laravel.com/docs/5.8/scout)  

## Priority Handlers  
Searchman is built around indexing priorities. By default, two priority handlers  
are available for use

* Nwogu\SearchMan\PriorityHandlers\LocationPriorityHandler  
* Nwogu\SearchMan\PriorityHandlers\LongTextPriorityHandler  

You can specify which handler to use for your indexing by defining the method  
```getIndexablePriorities``` on your indexable model.  It should return an array  
specifing the column name and the handler.

```
    public function getIndexablePriorities()
    {
        return [
            'minutes' => LongTextPriorityHandler::class,
            'email' => LocationPriorityHandler::class
        ];
    }

```  
By default, the LocationPriorityHandler is used for all indexing. you can  
overide this in the searchman config file.  
Building your own handlers is easy. Implement the Priority handler Interface and you are good to go.  

## Searching  
Laravel Scout only suports strict where clauses. but with Searchman, you can specify the operation of  
your ```where``` statements using ```:```.  

```
App\Meeting::search("discussion on health")->where("attendance:>", 10)->get();

```  
For more on search, look up the [Laravel Scout Documentation](https://laravel.com/docs/5.8/scout)  

## Results
Calling get on your query would return an Eloquent collection of models sorted by the priority attribute.  

```  
    {#3017
        +"id": 9,
        +"society_id": 1,
        +"name": "General Meeting Thursday, 21 Mar, 2019",
        +"type": "general meeting",
        +"minute": "<p>tjlkj;km;</p>",
        +"start_time": "2019-03-18 14:00:00",
        +"end_time": "2019-03-18 17:00:00",
        +"presider": 1,
        +"total_attendance": 1,
        +"created_at": "2019-03-18 19:16:01",
        +"updated_at": "2019-03-18 19:16:01",
        +"meeting_date": "2019-03-21 20:19:00",
        +"priority": 3.0,
        +"document_id": 9,
        +"priotity": 7.5
    }
```



