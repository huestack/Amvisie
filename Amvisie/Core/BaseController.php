<?php

declare(strict_types=1);

namespace Amvisie\Core;

/**
 * An abstract representation of any controller class implemented here.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
abstract class BaseController implements ControllerInterface
{
    /**
     * An instance of Route class that has extracted information from URL.
     * @var \Amvisie\Core\Route
     */
    private $route;

    /**
     * A proper case name of view.
     * @var string 
     * @internal A method name parsed from route may not match the case of actual method name.
     * So we need to get the name of view from method by discarding HTTP method prefix.
     */
    private $viewName;

    /**
     * An array having data flowed from controller to view apart from model.
     * @var array A key-value pair array. 
     */
    protected $viewPocket;

    /**
     * An object that has dynamic properties.
     * @var stdClass 
     */
    protected $viewObject;

    /**
     * A temporary data that flows between redirect.
     * @var \Amvisie\Core\Web\TempDataArray 
     */
    protected $tempPocket;

    /**
     * A reference to HttpRequest class that contains request data. 
     * @var \Amvisie\Core\HttpRequest
     */
    private $request;

    /**
     * A reference to HttpRequest class that holds response data.
     * @var \Amvisie\Core\HttpResponse
     */
    private $response;

    /**
     * Gets an object that contains HTTP request data.
     * @return \Amvisie\Core\HttpRequest A request object.
     */
    public function &request(): \Amvisie\Core\HttpRequest
    {
        return $this->request;
    }

    /**
     * Gets an object that handles HTTP response data.
     * @return \Amvisie\Core\HttpResponse A response object.
     */
    public function &response(): \Amvisie\Core\HttpResponse
    {
        return $this->response;
    }

    /**
     * Gets a reference to Route class that contains parsed url.
     * @return \Amvisie\Core\Route
     */
    public function &getRoute(): \Amvisie\Core\Route
    {
        return $this->route;
    }

    /**
     * Sets a reference to Route class.
     * @param \Amvisie\Core\Route $route A reference to Route object.
     */
    public function setRoute(\Amvisie\Core\Route &$route): void
    {
        $this->route = $route;
    }

    /**
     * <b>Infrastructure method.</b>
     * Initiates call of controller's method.
     * @return bool An indication that method invocation of controller has been successful.
     */
    public function invoke(): bool
    {
        // Cannot trust on BaseController's constructor because PHP doesn't call base class 
        // constructor if __construct() is defined in derived class. So the initialization is done here.
        $context = AppContext::getContext();
        
        $this->route = $context->route();
        $this->request = $context->request();
        $this->response = $context->response();
        
        $this->viewObject = new \stdClass();
        $this->viewPocket = array();
        $this->tempPocket = new Web\TempDataArray();
        
        // Concat HTTP method, i.e GET or POST, with name of class method parsed from Route. It will be actual name of method to invoke, such as getIndex().
        $methodName = $this->request->getMethod() . $this->route->getMethod();

        // Intialize Reflection object of current controller.
        $reflector = new \ReflectionClass($this);

        // Check if requested method is available in current controller.
        if ($reflector->hasMethod($methodName)) {
            // Get ReflectionMethod object for method.
            $method = $reflector->getMethod($methodName);

            // Check if method is in public use. Otherwise generate 404 status.
            if ($method->isPublic()) {

                // Since method is available, a default view name is set.
                $this->viewName = substr($method->name, strlen($this->request->getMethod()));

                // The method can be invoked. 
                $arguments = $this->getArguments($method);

                $this->processResponse($method, $arguments);

                return true;
            }
        }

        return false;
    }

    // Protected Functions.

    /**
     * Redirects to given url.
     * @param string $url A string containing url to redirect to.
     * @return \Amvisie\Core\Web\RedirectResponse
     */
    protected function redirect(string $url): Web\RedirectResponse
    {
        return new Web\RedirectResponse($url);
    }

    /**
     * Redirects to specified method of controller.
     * @param string $method Name of method to redirect to.
     * @param string $controller Name of controller where this method is implemented.
     * @param array $routeData Any route data to transfer to method in redirect request.
     * @return \Amvisie\Core\Web\RedirectResponse
     */
    protected function redirectToMethod(string $method, string $controller = null, array $routeData = array()): Web\RedirectResponse
    {
        return new Web\RedirectResponse($this->route->getRouteUrl($method, $controller, $routeData));
    }

    /**
     * Creates a view response with master layout.
     * @param string $name [optional] A name of view. If name is not provided, current method name is used as a view name. View names are case sensitive.
     * @param mixed $model [optional] A model to bind view with.
     * @return \Amvisie\Core\Web\ViewResponse A reference to object of ViewResponse class.
     */
    protected function view(string $name = null, $model = null): Web\ViewResponse
    {
        if ($name == null) {
            $name = $this->viewName;
        }

        $viewContext = new Web\ViewContext($this->route, $this->viewObject, $this->viewPocket, $this->tempPocket, $this->request, $this->response);

        $view = new Web\View($name, $viewContext, $model);

        return new Web\ViewResponse($view);
    }

    /**
     * Creates a view response without master layout.
     * @param string $name [optional] A name of view. If name is not provided, current method name is used as a view name. View names are case sensitive.
     * @param mixed $model [optional] A model to bind view with.
     * @return \Amvisie\Core\Web\ViewResponse A reference to object of ViewResponse class.
     */
    protected function partialView(string $name = null, $model = null): Web\ViewResponse
    {
        if ($name == null) {
            $name = $this->viewName;
        }

        $viewContext = new Web\ViewContext($this->route, $this->viewObject, $this->viewPocket, $this->tempPocket, $this->request, $this->response);

        $view = new Web\View($name, $viewContext, $model, false);

        return new Web\ViewResponse($view);
    }

    /**
     * Loads a view without master layout and returns the content.
     * @param string $name A name of view to load. If null, name is identified based on current method. View names are case sensitive.
     * @param mixed $model An reference of model object.
     * @return string A content created after rendering view.
     */
    protected function partialViewContent(string $name = null, $model = null): string
    {
        if ($name == null) {
            $name = $this->viewName;
        }

        $viewContext = new Web\ViewContext($this->route, $this->viewObject, $this->viewPocket, $this->tempPocket, $this->request, $this->response);

        $view = new Web\View($name, $viewContext, $model, false);

        ob_start();
        $view->render();
        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Generates JSON response.
     * @param object $data An array or object that has to be serialized into JSON.
     * @return \Amvisie\Core\Web\JsonResponse An instance of JsonResponse object.
     */
    protected function json($data): Web\JsonResponse
    {
        return new Web\JsonResponse($data);
    }

    /**
     * Invoked before call of requested method.
     * @param \Amvisie\Core\MethodContext $context
     */
    protected function beforeMethodCall(MethodContext $context)
    {
        // Currently the implementation seems to be vague here.
    }

    private function getArguments(\ReflectionMethod &$method) : array
    {
        $arguments = array();

        // Loop through each parameter, find the type and intialize proper instances.
        $params = $method->getParameters();

        foreach ($params as $param) {
            if ($param->isPassedByReference()){
                throw new \Exception("Cannot pass '" . $param->name . "' as a reference in " . $method->name . " of " . $method->class);
            }
                
            /* @var $param \ReflectionParameter */
            $data = null;

            $paramClass = $param->getClass();

            if ($paramClass != null) {
                $data = $this->getClassRequestData($paramClass);
            } else if ($param->isArray()) {
                $data = $this->getArrayRequestData($param);
            } else {
                $data = $this->getScalarRequestData($param->name);
            }

            if( $data == null) {
                $data = $param->getDefaultValue();
            }
            
            $arguments[$param->name] = $data;
        }

        return $arguments;
    }

    /**
     * Gets a value or array available in HTTP request in querystring, body, or url route.
     * @param string $paramName A name of parameter.
     * @return mixed A value or an array.
     */
    private function getScalarRequestData(string $paramName)
    {
        $data = null;

        if ($this->request->hasKey($paramName)) {
            $data = $this->request->param($paramName);
        } else {
            $data = $this->route->getRouteData($paramName);
        }

        return $data;
    }

    /**
     * Gets an object of the class represented by the parameter.
     * @param \ReflectionClass $paramClass
     * @return object An object of a class.
     */
    private function getClassRequestData(\ReflectionClass &$paramClass)
    {
        $converter = $this->request->converter();
        if ($converter == null) {
            return null; 
        }
        
        $object = $converter->convertAs($paramClass);

        $routeData = $this->route->getRouteDataArray();

        foreach ($routeData as $key => $value) {
            if (property_exists($object, $key)) {
                $object->{$key} = $value;
            }
        }

        if ($object instanceof BaseModel) {
            $object->validate();
        }

        return $object;
    }

    private function getArrayRequestData(\ReflectionParameter $param): array
    {
        $data = null;

        if ($this->request->hasKey($param->name)) {
            $data = $this->request->param($param->name);
        } else {
            $data = $this->route->getRouteData($param->name);
        }

        if ($data !== null && !is_array($data)) {
            $data = array($data);
        }

        return $data;
    }

    private function processResponse(\ReflectionMethod $method, array $arguments = array()): void
    {
        $methodContext = new MethodContext();

        $this->beforeMethodCall($methodContext);

        if ($methodContext->response === null) {
            $methodContext->response = $method->invokeArgs($this, $arguments);
        }

        $this->response->flushHeaders();

        // A method may not return any of BaseResponse object.
        if ($methodContext->response != null) {
            $methodContext->response->process();
        }
    }
}
