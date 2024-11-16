<?php

namespace BaseDto;

class Generator
{
    public function parse(string $file){
        return yaml_parse_file($file);
    }

    public function generate(string $file){
        $data = $this->parse($file);
        preg_match('/.*\/(?<className>[^\.]*)\.yaml/', $file, $matches);
        $destinationFilePath = preg_replace('/\.yaml/', '.php',$file);
        $skipKeys = [
            '$schema',
            'namespace',
            'properties'
        ];
        $blank = $this->blankClass();
        $blank = preg_replace('/{{namespace}}/', $data['namespace'], $blank);
        $blank = preg_replace('/{{class}}/', $matches['className'], $blank);
        $gettersSetters = [];
        $properties = [];
        $serializeUsed = false;
        foreach($data['properties'] as $key => $value){
            if($this->isScalarType($value['type'])){
                if(isset($value['serialize'])){
                    $serializeUsed = true;
                    $properties[] = preg_replace(['/{{type}}/', '/{{property}}/', '/{{serialize}}/'], [$value['type'], $key, $value['serialize']], $this->blankPropertySerialize());
                } else {
                    $properties[] = preg_replace(['/{{type}}/', '/{{property}}/'], [$value['type'], $key], $this->blankProperty());
                }
                $gettersSetters[] = preg_replace(['/{{UCkey}}/', '/{{key}}/', '/{{type}}/'], [ucfirst($key), $key, $value['type']], $this->blankGetterSetter());
            } else {
                $this->generateSubClass();
            }
        }

        $temp = implode(PHP_EOL, $gettersSetters);
        $temp2 = implode(PHP_EOL, $properties);

        $blank = preg_replace('/{{gettersSetters}}/', $temp, $blank);
        $blank = preg_replace('/{{properties}}/', $temp2, $blank);
        $uses = '';
        if($serializeUsed){
            $uses = 'use BaseDto\Serialize;';
        }
        $blank = preg_replace('/{{uses}}/', $uses, $blank);

        file_put_contents($destinationFilePath, $blank);
    }

    protected function generateSubClass()
    {
//        mkdir(, 0644);
    }

    protected function isScalarType(string $type){
        return in_array($type, ['string', 'int', 'float', 'bool']);
    }

    protected function blankProperty(){
        return
            <<<PROPERTY
            protected ?{{type}} \${{property}} = null;
        PROPERTY;
    }
    protected function blankPropertySerialize(){
        return
            <<<PROPERTY
            #[Serialize('{{serialize}}')]
            protected ?{{type}} \${{property}} = null;
        PROPERTY;
    }
    protected function blankGetterSetter(){
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
    protected function blankClass(){
        return
            <<<BLANK
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
        BLANK;
    }
}