<?php

namespace Amvisie\Core\Api;

/**
 * Generates Internal Server Error response.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class ResponseException extends \LogicException
{
    
    /**
     * 
     * @param int $statusCode
     * @param string $message
     * @param \Exception $inner
     */
    public function __construct(int $statusCode, string $message, \Exception $inner)
    {
        parent::__construct();
    }
}
