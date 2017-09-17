<?php

namespace Amvisie\Core\Annotations;

/**
 * Represents meta data of model.
 * @author Ritesh GIte, huestack@yahoo.com
 */
class ModelMeta
{
    private $attributes = array();
    
    private $propertyTypeInfos = [];


    /**
     * Adds a property and its attributes.
     * @param string $property A name of model's property.
     * @param array $attributes An array of any object that could be used by model.
     */
    public function addAttributes(string $property, array $attributes)
    {
        $this->attributes[$property] = $attributes;
    }
    
    public function addPropertyTypeInfo(string $property, PropertyTypeInfo $info)
    {
        $this->propertyTypeInfos[$property] = $info;
    }

    /**
     * Gets all properties and their rules.
     * @return array A associative array of property-rules pair.
     */
    public function getAttributes($property) : array
    {
        return $this->attributes[$property];
    }
    
    public function attributes() : array
    {
        return $this->attributes;
    }
    
    /**
     * Returns type info associated with given property name.
     * @param string $property Name of property.
     * @return PropertyTypeInfo
     */
    public function getPropertyTypeInfo($property) : ?\Amvisie\Core\Annotations\PropertyTypeInfo
    {
        return array_key_exists($property, $this->propertyTypeInfos) ? $this->propertyTypeInfos[$property] : null;
    }
    
    public function propertyTypeInfos() : array
    {
        return $this->propertyTypeInfos;
    }
}
