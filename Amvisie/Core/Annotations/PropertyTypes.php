<?php

namespace Amvisie\Core\Annotations;

class PropertyTypes
{
    CONST STRING = 0;
    CONST INTEGER = 2;
    CONST DOUBLE = 3;
    CONST BOOL = 4;
    CONST DATETIME = 5;
    CONST ARR = 6;
    CONST OBJ = 7;
    
    private function __construct() {
        // Empty. Do not allow instantiation.
    }
}
