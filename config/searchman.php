<?php

use Nwogu\SearchMan\PriorityHandlers\LocationPriorityHandler;

return [

    "default_priority_handler" => LocationPriorityHandler::class,


    "indexable_length" => 5,


    "connection" => null,

    
    "suffix" => "_index"

];