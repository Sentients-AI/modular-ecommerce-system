<?php

declare(strict_types=1);

namespace App\Shared\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseData implements Arrayable, JsonSerializable
{
    /**
     * Convert the DTO to an array.
     *
     * @return array<string, mixed>
     */
    final public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->{$name};

            if ($value instanceof Arrayable) {
                $data[$name] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$name] = array_map(
                    fn ($item) => $item instanceof Arrayable ? $item->toArray() : $item,
                    $value
                );
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Convert the DTO to JSON serializable array.
     *
     * @return array<string, mixed>
     */
    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
