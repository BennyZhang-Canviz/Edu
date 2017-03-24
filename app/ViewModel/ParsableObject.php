<?php

namespace App\ViewModel;


use ReflectionClass;

abstract class ParsableObject
{
    /**
     * Parse json data to the object
     *
     * @param string $json The json data
     *
     * @return void
     */
    public function parse($json)
    {
        $map = collect($this->mappingTable);
        $data = collect($json);
        if ($map->isEmpty() or $data->isEmpty())
        {
            return;
        }
        foreach($map as $key => $value)
        {
            if (!$data->has($value))
            {
                continue;
            }
            $class = new ReflectionClass(get_class($this));
            if ($class->hasProperty($key))
            {
                $class->getProperty($key)->setValue($this, $data[$value]);
            }
        }
    }

    /**
     * The table defining how to map the json data to the object properties
     *
     * @var array
     */
    protected $mappingTable;
}