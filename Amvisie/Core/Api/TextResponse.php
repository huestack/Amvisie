<?php

namespace Amvisie\Core\Api;

/**
 * Generates plain text response.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class TextResponse extends BaseApiResponse
{
    public function __construct($content = null)
    {
        $this->content = $content;
    }
    
    public function process() : void
    {
        header('HTTP/1.1 ' . $this->getStatus() . ' ' . self::getStatusText($this->getStatus()));
        header('Content-type: text/plain');
        if ($this->content){
            echo $this->content;   
        }
    }
}
