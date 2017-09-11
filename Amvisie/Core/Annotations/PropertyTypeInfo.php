<?php

namespace Amvisie\Core\Annotations;

/**
 * Enforces that the data in properties must be of specified data type.
 * @todo Not complete yet.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class PropertyTypeInfo
{
    
    /**
     *
     * @var DataType 
     */
    private $type;
    
    private $mixed;


    /**
     * Initiates a new instance of DataTypeRule class.
     * @param int $dataType
     * @param mixed $info
     */
    public function __construct(int $dataType = DataType::HTML_ENCODED_STRING, $mixed = null)
    {
        $this->type = $dataType;
        $this->mixed = $mixed;
    }
    
    /**
     * Returns one of the constant value of DataType class
     * @return int 
     */
    public function getType() : int
    {
        return $this->type;
    }
    
    public function getInfo(){
        return $this->mixed;
    }
}

class DataType{
    CONST STRING = 0;
    CONST INTEGER = 2;
    CONST DOUBLE = 3;
    CONST BOOL = 4;
    CONST DATETIME = 5;
    CONST ARR = 6;
    CONST OBJ = 7;
}