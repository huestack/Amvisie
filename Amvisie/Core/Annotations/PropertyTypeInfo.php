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
     * @var PropertyTypes 
     */
    private $type;
    
    private $mixed;


    /**
     * Initiates a new instance of DataTypeRule class.
     * @param int $dataType
     * @param mixed $info
     */
    public function __construct(int $dataType = PropertyTypes::STRING, $mixed = null)
    {
        $this->type = $dataType;
        $this->mixed = $mixed;
    }
    
    /**
     * Returns one of the constant value of PropertyTypes class
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