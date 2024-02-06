<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Client\Factory;

class SpecimenTracking extends Facade
{
    
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}