<?php

namespace App\Repositories\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class Collection extends Model{
    protected $connection = 'mongodb';
    protected $collection = null;

    public function __construct($collectionName)
    {
        $this->collection = $collectionName;
    }
}