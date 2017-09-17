<?php

namespace Amvisie\Core\RequestConverters;

use Amvisie\Core\Annotations\PropertyTypes;

/**
 * Converts json to custom object.
 * @author Ritesh
 */
class JsonConverter extends BaseConverter
{
    private $isArray;
    
    public function parse() : bool
    {
        $content = ltrim(file_get_contents('php://input'));
        
        if (strlen($content) === 0) {
           return true; 
        }
        
        $this->isArray = $content[0] === '[';
        
        $this->data = json_decode($content, true);
        
        return $this->data !== null;
    }
    
    /**

     * @param \ReflectionClass $object
     */
    public function convertAs(\ReflectionClass $object)
    {
        if ($object->isSubclassOf(\Amvisie\Core\BaseModel::class)){
            $instance = $this->isArray ?
                        $this->getModelArray($this->data, $object) :
                        $this->getModel($this->data, $object);
            $this->data = null;
            
            return $instance;
        } else {
            return $this->getObject($object);
        }
    }
    
    /**
     * 
     * @param \ReflectionClass $object
     * @return object An instance of a class associated with \ReflectionClass parameter.
     */
    private function getObject(array $array, \ReflectionClass $object) 
    {
        $instance = $object->newInstance();
        foreach ($array as $key => $value) {
            $property = $object->getProperty($key);
            if ($property && $property->isPublic() && !$property->isStatic()) {
                if (is_array($value)) {
                    $instance->{$key} = $this->getArray($value);
                } else {
                    $instance->{$key} = htmlspecialchars($value);
                }
            }
        }
        
        return $instance;
    }
    
    private function getArray(array $array) : array
    {
        foreach ($array as $key => $value){
            if (is_array($value)) {
                $this->getArray($value);
            } else {
                $array[$key] = htmlspecialchars($value);
            }
        }
        return $array;
    }
    
    private function getModel(array $array, \ReflectionClass $object)
    {
        /* @var $instance \Amvisie\Core\BaseModel */
        $instance = $object->newInstance();
        foreach($array as $key => $value){
            $this->setProperty($object, $key, $value, $instance);
        }
        
        return $instance;
    }
    
    /**
     * 
     * @param array $array
     * @todo Not complete yet. Make sure to call getPropertyTypeInfo to gather property information.
     */
    private function getModelArray(array $objectArray, \ReflectionClass $class)
    {
        if ($class->isSubclassOf(\Amvisie\Core\BaseModel::class)) {
            $instances = [];
            
            // $objectArray is an array that is either returned by property or is root array.
            foreach ($objectArray as $object) {
                $instance = $class->newInstance();
                foreach ($object as $propertyName => $propertyValue) {
                    /* @var $property \ReflectionProperty  */
                    //$property = $class->hasProperty($propertyName) ? $class->getProperty($propertyName) : null;
                    $this->setProperty($class, $propertyName, $propertyValue, $instance);
                }
                
                $instances[] = $instance;
            }
            
            return $instances;
        } else {
            return $this->getObjectArray($objectArray, $class);
        }
    }
    
    private function setProperty(\ReflectionClass $object, $key, $value, $instance)
    {
        $property = $object->hasProperty($key) ? $object->getProperty($key) : null;
        if ($property && $property->isPublic() && !$property->isStatic()) {
            if (is_object($value) || is_array($value)) {
                // $value could be an array or object. Get PropertyTypeInfo and determine the type.
                $propertyTypeInfo = $instance->getMeta()->getPropertyTypeInfo($key);
                
                if ($propertyTypeInfo && $propertyTypeInfo->getType() === PropertyTypes::ARR) {
                    // $value is an array. Get the type (RelfectionClass) of the array.
                    $class = $propertyTypeInfo->getInfo();
                    if (!$class) {
                        // There is no type associated with the array property. Set the array as it is.
                        $property->setValue($instance, $this->getArray($value));
                    } else {
                        // Array is of proper type.
                        $property->setValue($instance, $this->getModelArray($value, $class));
                    }
                } else if ($propertyTypeInfo && $propertyTypeInfo->getType() === PropertyTypes::OBJ) {
                    // $value is of object type.
                    $class = $propertyTypeInfo->getInfo();
                    if ($class){
                        $property->setValue($instance, $this->getModel($value, $class));
                    } else {
                        $property->setValue($instance, $this->getObject($value, $class));
                    }
                } else {
                    $property->setValue($instance, $this->getObject($value, $class));
                }
            } else {
                $property->setValue($instance, htmlspecialchars($value));
            }
        }
    }
    
    private function getObjectArray(array $array, \ReflectionClass $class)
    {
        $instances = [];
        foreach($array as $valueArray) {
            $instance = $class->newInstance();

            foreach ($valueArray as $valueKey => $valueValue) {
                /* @var $property \ReflectionProperty  */
                $property = $class->hasProperty($valueKey) ? $class->getProperty($valueKey) : null;
                
                if ($property && $property->isPublic() && !$property->isStatic()) {
                    if (is_array($valueValue)) {
                        $property->setValue($instance, $this->getArray($valueValue));
                    } else {
                        $property->setValue($instance, htmlspecialchars($valueValue));
                    }
                }
            }

            $instances[] = $instance;
        }

        return $instances;
    }
}
