<?php

namespace BaseDto;

use JsonSerializable;

abstract class BaseDto implements JsonSerializable
{
    public function fromArray(array $data): void
    {
        $fieldsMap = $this->getFieldsMap();
        foreach ($data as $key => $value) {
            foreach ($fieldsMap as $fieldItem) {
                if ($this->isScalar($value)) {
                    if ($fieldItem['serialize'] === $key) {
                        $this->{$fieldItem['name']} = $value;
                        break;
                    }
                } else {
                    if($fieldItem['serialize'] === $key){
                        $subClass = new $fieldItem['type']();
                        $subClass->fromArray($value);
                        $this->{$fieldItem['name']} = $subClass;
                        break;
                    }
                }
            }
        }
    }

    protected function isScalar(mixed $value): bool
    {
        return is_scalar($value);
    }

    private function getFieldsMap(): array
    {
        $reflection = new \ReflectionClass(get_class($this));

        $properties = $reflection->getProperties();
        $fieldsMap = [];
        foreach ($properties as $property) {
            $attributes = $property->getAttributes();
            $fieldsMap[] = [
                'name' => $property->getName(),
                'serialize' => !empty($attributes) ? $attributes[0]?->getArguments()[0] ?? $property->getName() : $property->getName(),
                'type' => $property->getType()->getName(),
            ];
        }

        return $fieldsMap;
    }


    public function jsonSerialize(): mixed
    {
        $fieldsMap = $this->getFieldsMap();
        $result = [];
        foreach ($fieldsMap as $fieldItem) {
            $result[$fieldItem['serialize']] = $this->{$fieldItem['name']};
        }
        return $result;
    }
}