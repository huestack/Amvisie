<?php
namespace Amvisie\Core\Web;

/**
 * Generates a redirect header to send to browser or connected client.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class RedirectResponse extends BaseResponse
{
    private $url;
    
    private $permanent;
    
    public function __construct(string $url, bool $permanent = false)
    {
        $this->url = $url;
        $this->permanent = $permanent;
    }
    
    public function process()
    {
        if ($this->permanent){
            header('HTTP/1.1 301 Moved Permanently');
        }
        
        header('Location: ' . $this->url);
    }    
}
