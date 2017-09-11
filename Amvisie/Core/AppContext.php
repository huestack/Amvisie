<?php

namespace Amvisie\Core;

/**
 * Description of AppContext
 *
 * @author ritesh
 */
final class AppContext
{
    
    // -- Statics Start
    /**
     * @var \Amvisie\Core\AppContext 
     */
    private static $current;
    
    public static function &getContext() : AppContext
    {
        return self::$current;
    }
    
    public static function setContext(?AppContext $context) : void
    {
        self::$current = $context;
    }
    
    // -- Statics end
    
    /**
     * @var \Amvisie\Core\HttpRequest 
     */
    private $request;
    
    /**
     * @var \Amvisie\Core\HttpResponse
     */
    private $response;
    
    /**
     * @var \Amvisie\Core\Route
     */
    private $route;


    public function __construct(Route $route, HttpRequest $request, HttpResponse $response)
    {
        $this->route = $route;
        $this->request = $request;
        $this->response = $response;
    }
    
    public function &request() : \Amvisie\Core\HttpRequest 
    {
        return $this->request;
    }
    
    public function &response() : \Amvisie\Core\HttpResponse
    {
        return $this->response;
    }
    
    public function &route() : \Amvisie\Core\Route
    {
        return $this->route;
    }
}
