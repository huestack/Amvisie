<?php
declare(strict_types=1);

namespace Amvisie\Core;

require 'Configuration.php';

/**
 * A core application that handles autoload and initiates flow to controllers, and actions.
 * When this class is inherited, BootLoader tries to instantiate object of derived class based on settings provided in CONFIG file.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class Application
{
    /**
     * @var \Amvisie\Core\Route
     */
    private $route;
    
    /**
     * @var \Amvisie\Core\HttpRequest 
     */
    private $request;
    
    /**
     * @var \Amvisie\Core\HttpResponse
     */
    private $response;
    
    /**
     * An array to store methods to invoke during autoload. These methods may help to location Model, Resources, 
     * and other user defined classes.
     * @var array 
     */
    private $locators = array();

    /**
     * It can be overridden in derived class to define the settings before controller and action invokes.
     */
    public function init()
    {
        // Acts as a placeholder for a virtual call.
    }
    
    /**
     * It can be overridden in derived class to catch the end of application process.
     */
    public function end()
    {
        // Acts as a placeholder for a virtual call.
    }

    /**
    * A virtual method call after the request is initialized.
    */
   public function beginRequest()
   {
       // Acts as a placeholder for a virtual call. Cannot make it abstract as beginRequest is not required in derived class.
   }
    
    final public function &request() : HttpRequest 
    {
        if ($this->request == null) {
            throw new Exception('HttpRequest is not available in init method of Application.');
        }

        return $this->request;
    }
    
    final public function &response() : HttpResponse 
    {
        if ($this->response == null) {
            throw new \Exception('HttpResponse is not available in init method of Application.');
        }

        return $this->response;
    }
    
    /**
     * Sets a callback to be executed when system is looking for classes.
     * The callable must have two parameters for name of class (without namespace), and qualified name of class respectively.
     * The callable must return path of file where model class is available, or null if class is not found.
     * @param callable $callback
     */
    final protected function setLocator(callable $callback)
    {
        $this->locators[] = $callback;
    }
    
    /**
     * Parses route uri and locates associated controller file.
     * @internal Infrastructure method. Not supposed to be invoked by user.
     */
    private function startProcess() 
     {
        $this->registerAutoload();
        
        $this->registerControllerFactory();
        
        // A virtual call to make sure the derived class's init() is invoked.
        $this->init();
        
        $this->request = new HttpRequest();
        $this->response = new HttpResponse();
        $this->route = Route::parse($this->request->getUrl(), ALIAS);
        
        if ($this->route == null) {
            $this->error404("Url doesn't match with any route added in route collection.");
        } else {
            
            AppContext::setContext(new AppContext($this->route, $this->request, $this->response));

            // A virtual call to notify that request is initialized.
            $this->beginRequest();

            $this->invokeController();
            
            $this->response->end();
        }
    }
    
    private function invokeController()
    {
        try {
            $builder = ControllerBuilder::current();
            
            if (!$builder->build()->invoke()) {
                $this->error404($this->route->getRouteUrl() . ' not found.');
            }
        } catch (\Exception $ex) {
            $this->error500($ex->getMessage() . '\r\n\r\n' . $ex->getTraceAsString());
        }
    }
    
    /**
     * @internal Infrastructure method. Do not call in your code.
     */
    public function shutDown()
    {
        $this->request = null;
        $this->response = null;
        
        AppContext::setContext(null);
        
        $this->end();
    }
    
    private function error404(string $message)
    {
        $this->response()->setHttpHeader(404, 'Not Found');
        $this->response()->flushHeaders();
        echo $message;
    }
    
    private function error500(string $message)
    {
        $this->response()->setHttpHeader(500, 'Internal Server Error');
        $this->response()->flushHeaders();
        echo $message;
    }
    
    /**
     * Registers autoload method.
     */
    private function registerAutoload() : void
    {
        spl_autoload_register(array($this, 'classLoad'));
        
        register_shutdown_function(array($this, 'shutDown'));
    }
    
    private function registerControllerFactory() : void
    {
        ControllerBuilder::current()->setControllerFactory(new ControllerFactory());
    }
    
    /**
     * An autoload function registered in registerAutoload method.
     * @param string $class A qualified name of class, which is being instantiated or referred.
     */
    private function classLoad(string $class)
    {
        $classParts = explode('\\', $class);
        $name = $classParts[count($classParts) - 1];
        
        foreach ($this->locators as $cb) {
            // Pass name without namespace as first argument, and full qualified name as second argument.
            $file = $cb[0]->{$cb[1]}($name, $class);
            if ($file != null) {
                require $file;
                return;
            }
        }
    }

    public static function bootstrap() : void
    {
        spl_autoload_register([self::class, 'coreAutoLoad']);

        /*@var $app Amvisie\Core\Application */
        $app = null;

        // My.config.php is a user defined configuration file that defines application level settings.
        // Load the file if it exists.
        if (file_exists(CONFIG)) {
            require CONFIG;
            
            // My.config.php may also contain name of a class derived from Amvisie\Core\Application.
            // Name of user's application file must be Global.php, and the name of class is user defined.
            // However, the name of class should be defined in GLOBAL_CLASS constant.
            if (defined('GLOBAL_CLASS') && file_exists(GLOBAL_FILE)) {
                require GLOBAL_FILE;

                $globalClass = GLOBAL_CLASS;
                $app = new $globalClass();
            } else {

                // User defined application class is not found. Instantiate core application class.
                $app = new Application();
            }
        } else {

            // My.config.php doesn't exist, and possibility of GLOBAL_CLASS defined is zero.
            // Instantiate core application class.
            $app = new Application();
        }

        $app->startProcess();
    }

    private static function coreAutoLoad($class) : void
    {
        $file = APP_PATH . str_replace('\\', '/', $class) . EXTN;

        require $file;

        // If a loading class is derived from Amvisie\Core\BaseResource, init method must be invoked to make sure
        // that all keys are initialized before being used in static "get" method.
        $reflection = new \ReflectionClass($class);
        if ($reflection->isSubclassOf(\Amvisie\Core\BaseResource::class)) {
            $reflection->getMethod('init')->invoke(null, $reflection);
        }
    }
}
