<?php

namespace BaseDto;

class Test extends BaseDto
{
    protected $id;
    #[Serialize('surname')]
    protected $name;
}