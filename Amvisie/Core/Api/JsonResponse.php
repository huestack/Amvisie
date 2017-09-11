<?php

namespace Amvisie\Core\Api;

/**
 * Generates API response with JSON content.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class JsonResponse extends BaseApiResponse 
{
    public function __construct($content = null) 
    {
        $this->content = $content;
    }
    
    final public function process() 
    {
        header('HTTP/1.1 ' . $this->getStatus() . ' ' . parent::getStatusText($this->getStatus()));
        header('Content-type: application/json; charset=utf-8');
        
        if ($this->content){
            echo json_encode($this->content);
        }
    }
}
