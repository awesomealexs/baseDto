<?php
declare(strict_types=1);

namespace BaseDto\Gen\InitRequest;
use BaseDto\BaseDto;
use BaseDto\Serialize;


class test1 extends BaseDto
{
    protected ?string $value1 = null;
    #[Serialize('asd123')]
    protected ?int $value9 = null;
    public function getValue1(): ?string
    {
        return $this->value1;
    }
    
    public function setValue1(?string $key): self
    {
        $this->value1 = $key;
        
        return $this;
    }
    public function getValue9(): ?int
    {
        return $this->value9;
    }
    
    public function setValue9(?int $key): self
    {
        $this->value9 = $key;
        
        return $this;
    }
}