<?php

namespace BaseDto;

use JsonSerializable;

abstract class BaseDto implements JsonSerializable
{
    public function __construct()
    {
        $this->recursiveBaseInit();
    }

    private function recursiveBaseInit(): void
    {
        $fieldsMap = $this->getFieldsMap();
        foreach ($fieldsMap as $field) {
            if (!$this->isScalarType($field['type'])) {
                $this->{$field['name']} = new $field['type']();
                $this->{$field['name']}->recursiveBaseInit();
            }
        }
    }

    private function isScalarType(string $type): bool
    {
        return in_array($type, ['string', 'int', 'float', 'bool']);
    }

    public function fromArray(array $data): void
    {
        $fieldsMap = $this->getFieldsMap();
        foreach ($data as $key => $value) {
            foreach ($fieldsMap as $fieldItem) {
                if ($this->isScalarValue($value)) {
                    if ($fieldItem['serialize'] === $key) {
                        $this->{$fieldItem['name']} = $value;
                        break;
                    }
                } else {
                    if ($fieldItem['serialize'] === $key) {
                        $subClass = new $fieldItem['type']();
                        $subClass->fromArray($value);
                        $this->{$fieldItem['name']} = $subClass;
                        break;
                    }
                }
            }
        }
    }

    protected function isScalarValue(mixed $value): bool
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