<?php

namespace Amvisie\Core\RequestConverters;

/**
 * An abstract class for request data parsers.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
abstract class BaseConverter
{
    /**
     *
     * @var string
     */
    private $httpMethod;
    
    /**
     * An array in which the request data is held.
     * @var array
     */
    protected $data;
    
    /**
     * An array in which files' references are stored.
     * @var array 
     */
    protected $files = [];
    
    abstract function parse() : bool;

    /**
     * Copies values from array/object into properties of given object.
     * @param \ReflectionClass $object  A model into which into which the string has to be serialized.
     */
    abstract function convertAs(\ReflectionClass $object);
    
    /**
     * Gets the array in which the request data is held.
     * @return array
     */
    final public function &getData() : array
    {
        return $this->data;
    }
    
    final public function &getFiles() : array
    {
        return $this->files;
    }
    
    final public function getHttpMethod() : ?string
    {
        return $this->httpMethod;
    }
    
    final public function setHttpMethod(string $value)
    {
        $this->httpMethod = $value;
    }
}
