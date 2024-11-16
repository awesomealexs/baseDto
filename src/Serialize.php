<?php
namespace BaseDto;


use Attribute;

#[Attribute]
readonly class Serialize {
    public function __construct(private  readonly string $serialize) {
    }

}