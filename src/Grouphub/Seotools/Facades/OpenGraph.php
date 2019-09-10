<?php namespace Grouphub\Seotools\Facades;

use Illuminate\Support\Facades\Facade;

class OpenGraph extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'seotools.opengraph';
    }
}
