<?php

namespace Amvisie\Core\Web;

/**
 * Provides helper methods for view.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class ViewContext
{
    /**
     * A Route object that contains parsed url.
     * @var \Amvisie\Core\Route
     */
    private $route;
    /**
     * An array to contain data passed from controller to view.
     * @var array
     */
    public $viewPocket;
    
    /**
     * An object that has dynamic properties.
     * @var stdClass 
     */
    public $viewObject;


    /**
     * 
     * @var \Amvisie\Core\TempDataArray 
     */
    public $tempPocket;
    
    /**
     * An array that holds the name of directories where the views could be found.
     * @var array 
     */
    private $viewDirs = array();
    
    /**
     * 
     * @var \Amvisie\Core\HttpRequest
     */
    private $request;
    
    /**
     * 
     * @var \Amvisie\Core\HttpResponse
     */
    private $response;

    public function __construct(\Amvisie\Core\Route &$route, 
            \stdClass &$viewObject, 
            array &$viewPocket, 
            TempDataArray &$tempPocket, 
            \Amvisie\Core\HttpRequest &$request,
            \Amvisie\Core\HttpResponse &$response
    ) {
        $this->route = $route;
        $this->viewPocket = $viewPocket;
        $this->tempPocket = $tempPocket;
        $this->viewObject = $viewObject;
        $this->request = $request;
        $this->response = $response;
        
        //@todo: Confirm that view directories are generated properly.
        $this->viewDirs[] = $this->route->getDirectory() . VIEW_DIR . $this->route->getController(true) . '/';
        $this->viewDirs[] = $this->route->getDirectory() . VIEW_DIR . 'shared/';
        $this->viewDirs[] = APP_PATH . VIEW_DIR . 'shared/';
    }

    public function getViewDirectories() : array 
    {
        return $this->viewDirs;
    }
    
    /**
     * A reference to Route object.
     * @return \Amvisie\Core\Route
     */
    public function &getRoute() : \Amvisie\Core\Route
    {
        return $this->route;
    }
    
    /**
     * A reference to HttpRequest object that contains request data.
     * @return \Amvisie\Core\HttpRequest
     */
    public function &request() : \Amvisie\Core\HttpRequest
    {
        return $this->request;
    }
    
    /**
     * @return \Amvisie\Core\HttpResponse
     */
    public function &response() : \Amvisie\Core\HttpResponse
    {
        return $this->response;
    }
}
