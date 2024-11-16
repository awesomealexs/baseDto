<?php

namespace BaseDto;
use JsonSerializable;

abstract class BaseDto implements JsonSerializable
{
    public function fromArray(array $data)
    {
        $fieldsMap = $this->getFieldsMap();
        foreach ($data as $key => $value) {
            foreach ($fieldsMap as $fieldItem) {
                if($fieldItem['serialize'] === $key) {
                    $this->{$fieldItem['name']} = $value;
                    break;
                }
            }
        }
    }

    private function getFieldsMap():array
    {
        $reflection = new \ReflectionClass(get_class($this));

        $properties = $reflection->getProperties();
        $fieldsMap = [];
        foreach ($properties as $property) {
            $fieldsMap[] = [
                'name' => $property->getName(),
                'serialize' => isset($property->getAttributes()[0])?$property->getAttributes()[0]?->getArguments()[0] ?? $property->getName():$property->getName(),//@todo fix
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