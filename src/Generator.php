<?php

namespace BaseDto;

class Generator
{
    private function parseYaml(string $file): array
    {
        return yaml_parse_file($file);
    }

    public function generate(string $yamlFilePath): void
    {
        if (!is_file($yamlFilePath)) {
            throw new \RuntimeException("File {$yamlFilePath} not found");
        }

        $data = $this->parseYaml($yamlFilePath);
        if (empty($data)) {
            throw new \RuntimeException("Empty data from yaml file {$yamlFilePath}");
        }

        $namespace = $data['namespace'] ?? null;
        if ($namespace === null) {
            throw new \RuntimeException("Namespace in {$yamlFilePath} not specified");
        }

        $directoryPath = dirname($yamlFilePath);

        preg_match('/.*\/(?<className>[^\.]*)\.yaml/', $yamlFilePath, $matches);
        $className = $matches['className'];

        $destinationFilePath = $directoryPath . DIRECTORY_SEPARATOR . $className . '.php';

        $this->generateClass($data['properties'], $directoryPath, $namespace, $className, $destinationFilePath);

    }

    protected function generateClass(array $properties, string $directoryPath, string $namespace, string $className, string $destinationFilePath): void
    {
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0644);
        }
        $gettersSetters = [];
        $serializeUsed = false;
        $uses = [];
        $fileProperties = [];
        foreach ($properties as $propertyKey => $propertyData) {
            $notScalar = false;
            $type = $propertyData['type'];
            if (!$this->isScalarType($propertyData['type'])) {
                $subClassName = $propertyKey;
                $subClassNamespace = sprintf('%s\%s', $namespace, $className);
                $subClassDirectoryPath = $directoryPath . DIRECTORY_SEPARATOR . $className;
                $subClassDestinationFilePath = $subClassDirectoryPath . DIRECTORY_SEPARATOR . $subClassName . '.php';
                $uses[] = sprintf('use %s\%s;', $subClassNamespace, $subClassName);
                $this->generateClass($propertyData['properties'], $subClassDirectoryPath, $subClassNamespace, $subClassName, $subClassDestinationFilePath);
                $type = $subClassName;
                $notScalar = true;
            }
            if (isset($propertyData['serialize'])) {
                $serializeUsed = true;
                $fileProperties[] = preg_replace(['/{{type}}/', '/{{property}}/', '/{{serialize}}/'], [$type, $propertyKey, $propertyData['serialize']], $this->getBlankPropertySerialize());
            } else {
                $fileProperties[] = preg_replace(['/{{type}}/', '/{{property}}/'], [$type, $propertyKey], $this->getBlankProperty());
            }
            $gettersSetters[] = preg_replace(['/{{UCkey}}/', '/{{key}}/', '/{{type}}/'], [ucfirst($propertyKey), $propertyKey, $type], $this->getBlankGetterSetter());
        }


        $blank = $this->getBlankClass();
        $filled = preg_replace('/{{namespace}}/', $namespace, $blank);
        $filled = preg_replace('/{{class}}/', $className, $filled);
        $filled = preg_replace('/{{gettersSetters}}/', implode(PHP_EOL, $gettersSetters), $filled);
        $filled = preg_replace('/{{properties}}/', implode(PHP_EOL, $fileProperties), $filled);

        if ($serializeUsed) {
            $uses[] = 'use BaseDto\Serialize;';
        }
        $filled = preg_replace('/{{uses}}/', implode(PHP_EOL, $uses), $filled);

        file_put_contents($destinationFilePath, $filled);
    }

    protected function isScalarType(string $type): bool
    {
        return in_array($type, ['string', 'int', 'float', 'bool']);
    }

    protected function getBlankProperty(): string
    {
        return
            <<<PROPERTY
            protected ?{{type}} \${{property}} = null;
        PROPERTY;
    }

    protected function getBlankPropertySerialize(): string
    {
        return
            <<<PROPERTY_SERIALIZE
            #[Serialize('{{serialize}}')]
            protected ?{{type}} \${{property}} = null;
        PROPERTY_SERIALIZE;
    }

    protected function getBlankGetterSetter(): string
    {
        return
            <<<GETSET
            public function get{{UCkey}}(): ?{{type}}
            {
                return \$this->{{key}};
            }
            
            public function set{{UCkey}}(?{{type}} \$key): self
            {
                \$this->{{key}} = \$key;
                
                return \$this;
            }
        GETSET;
    }

    protected function getBlankClass(): string
    {
        return
            <<<BLANK_DTO
        <?php
        declare(strict_types=1);
        
        namespace {{namespace}};
        use BaseDto\BaseDto;
        {{uses}}
        
        
        class {{class}} extends BaseDto
        {
        {{properties}}
        {{gettersSetters}}
        }
        BLANK_DTO;
    }
}