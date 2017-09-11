<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amvisie\Core\RequestConverters;

/**
 * This converter parses url encoded data available in body into array and object.
 *
 * @author Ritesh
 */
class UrlEncodedConverter extends BaseConverter
{
    private $usePhpInputFor = array('put', 'patch', 'delete');
    
    public function convertAs(\ReflectionClass $object)
    {
        $instance = $object->newInstance();
        foreach ($this->data as $key => $value) {
            if ($object->hasProperty($key)) {
                $instance->{$key} = htmlspecialchars($value);
            }
        }
        
        return $instance;
    }
    
    public function parse() : void
    {
        if (array_search($this->getHttpMethod(), $this->usePhpInputFor) === false) {
            $this->data = filter_input_array(INPUT_POST);
        } else {
            parse_str(file_get_contents('php://input'), $this->data);
            
            foreach ($this->data as $key => $value) {
                $this->data[$key] = htmlspecialchars($value);
            }
        }
    }
}
