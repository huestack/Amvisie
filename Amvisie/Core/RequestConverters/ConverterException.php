<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amvisie\Core\RequestConverters;

/**
 * Description of ConverterException
 *
 * @author ritesh
 */
class ConverterException extends \LogicException
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) {
        if (count($message) == 0) {
            $message = "Conversion failed.";
        }
        
        parent::__construct($message, $code, $previous);
    }
}
