<?php

namespace PostProxy\Types;

class Model
{
    public function __construct(array $attrs = [])
    {
        foreach ($attrs as $key => $value) {
            $property = $this->snakeToCamel($key);
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    public function toArray(): array
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            $result[$this->camelToSnake($key)] = $value;
        }
        return $result;
    }

    private function snakeToCamel(string $key): string
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }

    private function camelToSnake(string $key): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', $key));
    }
}
