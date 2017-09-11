<?php
namespace Amvisie\Core\Web;

/**
 * Provides methods to generate url based on specified method, controller, and route data.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class UrlHelper
{
    /**
    * @var \Amvisie\Core\Route
    */
    private $route;
    
    public function __construct(\Amvisie\Core\Route &$route)
    {
        $this->route = $route;
    }
    
    /**
     * Makes a url based on specified route and returns as string.
     * @param string $method Name of method.
     * @param string $controller Name of controller.
     * @param array $routeData An array of route data.
     * @return string Route url.
     */
    public function getMethod(string $method, string $controller = null, array $routeData = array()) : string
    {
        return $this->route->getRouteUrl($method, $controller, $routeData);
    }
    
    /**
     * Makes a url based on specified route and echoes to client.
     * @param string $method Name of method.
     * @param string $controller Name of controller.
     * @param array $routeData An array of route data.
     */
    public function printMethod(string $method, string $controller = null, array $routeData = array()) : void
    {
        echo $this->route->getRouteUrl($method, $controller, $routeData);
    }
}
