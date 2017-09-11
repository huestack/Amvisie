<?php

namespace Amvisie\Core\Web;

/**
 * Generates a JSON response to send to browser or connected client.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class JsonResponse extends BaseResponse
{
    private $data;
    
    public function __construct($data)
    {
        header('Content-type: application/json');
        $this->data = $data;
    }
    
    public function process()
    {
        $data = json_encode($this->data);
        header("Content-length: " . strlen($data));
        echo $data;
    }    
}