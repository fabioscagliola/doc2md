<?php

namespace App\DataTransferObject;

class ConversionData
{
    public function __construct(

        public ?string $contents,

        public ?string $location,
    )
    {
    }
}

