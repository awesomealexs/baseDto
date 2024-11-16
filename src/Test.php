<?php

namespace BaseDto;

class Test extends BaseDto
{
    protected int $id;
    #[Serialize('surname')]
    protected string $name;

    #[Serialize('test1')]
    protected Test123 $variable1;
}