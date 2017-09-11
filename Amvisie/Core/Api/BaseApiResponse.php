<?php

namespace Amvisie\Core\Api;
/**
 * An abstract response class. All derived classes must implement process method.
 * @author Ritesh Gite <huestack@yahoo.com>
 */

abstract class BaseApiResponse implements \Amvisie\Core\ResponseInterface
{
    private static $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        301 => 'Moved Permanently',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        415 => 'Unsupported Media Type',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error'
    ];
    
    /**
     * Status code to send to client. Defaults to OK.
     * @var int 
     */
    private $statusCode = 200;
    
    /**
     *
     * @var mixed 
     */
    protected $content;

    /**
     * 
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function setStatus(int $code)
    {
        $this->statusCode = $code;
    }
    
    public function getStatus() : int
    {
        return $this->statusCode;
    }
    
    /**
     * Gets a HTTP status message.
     * @param int $code A status code.
     * @return string A status text.
     */
    public static function getStatusText(int $code) : string
    {
        return self::$statusTexts[$code];
    }
}
