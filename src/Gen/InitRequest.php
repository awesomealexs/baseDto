<?php
declare(strict_types=1);

namespace BaseDto\Gen;
use BaseDto\BaseDto;
use BaseDto\Gen\InitRequest\test1;
use BaseDto\Serialize;


class InitRequest extends BaseDto
{
    #[Serialize('user-account')]
    protected ?string $account = null;
    protected ?string $nonce = null;
    protected ?string $signature = null;
    #[Serialize('asd')]
    protected ?test1 $test1 = null;
    public function getAccount(): ?string
    {
        return $this->account;
    }
    
    public function setAccount(?string $key): self
    {
        $this->account = $key;
        
        return $this;
    }
    public function getNonce(): ?string
    {
        return $this->nonce;
    }
    
    public function setNonce(?string $key): self
    {
        $this->nonce = $key;
        
        return $this;
    }
    public function getSignature(): ?string
    {
        return $this->signature;
    }
    
    public function setSignature(?string $key): self
    {
        $this->signature = $key;
        
        return $this;
    }
    public function getTest1(): ?test1
    {
        return $this->test1;
    }
    
    public function setTest1(?test1 $key): self
    {
        $this->test1 = $key;
        
        return $this;
    }
}