<?php
declare(strict_types=1);

namespace Amvisie\Core;

use Amvisie\Core\Api\ResponseFactory;
use Amvisie\Core\Api\BaseApiResponse;

/**
 * An abstract representation of any api controller.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
abstract class BaseApiController implements ControllerInterface 
{
    
    /**
     * Initializes api-controller object, and objects for associated properties.
     * @internal Infrastructure method. Do not call directly.
     * @param \ReflectionClass $reflectionClass Reflection object of controller to be instantiated.
     * @param \Amvisie\Core\HttpRequest $request A request object.
     * @param \Amvisie\Core\HttpResponse $response A response object.
     * @param \Amvisie\Core\Route $route A route object.
     * @return \Amvisie\Core\BaseApiController A new instance of derived class of \Amvisie\Core\ControllerInterface.
     */
    public static function create(\ReflectionClass &$reflectionClass, HttpRequest &$request, HttpResponse &$response, Route &$route) : ControllerInterface
    {
        $instance = $reflectionClass->newInstance();
        $instance->reflector = $reflectionClass;
        $instance->route = $route;
        $instance->request = $request;
        $instance->response = $response;
        
        return $instance;
    }
    
    /**
     * An instance of ReflectionClass reporting information about sub class of BaseApiController.
     * @var \ReflectionClass
     */
    private $reflector;
    
    /**
     * An instance of Route class that has extracted information from URL.
     * @var Route
     */
    private $route;
    
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
     * Initiates call of controller's method.
     * @return boolean Returns true for ControllerFactory Call
     * @internal <b>Infrastructure method.</b>
     */
    public function invoke() : bool
    {
        $methodContext = new MethodContext();
        
        try {
            $matchedMethod = $this->getFinalMethod($this->reflector);

            // Check if requested method is available in current controller.
            // Get ReflectionMethod object for method.
            /*@var $method \ReflectionMethod */
            $method = $matchedMethod['name'];
            
            $parameters = $method->getParameters();

            $arguments = array();

            foreach ($parameters as $param) {
                /* @var $param \ReflectionParameter */
                $data = null;

                if ($param->isArray()){
                    $type = $this->getTypeFromPhpDoc($method->getDocComment(), $param->name);
                    
                    if ($type !== null){
                        $data = $this->request->converter()->convertAs($type);
                    }
                    else if ($this->request->hasKey($param->name)){
                        $data = $this->request->param($param->name);
                    }
                    else{
                        $data = $this->route->getRouteData($param->name);
                    }

                    if ($data !== null && !is_array($data)){
                        $data = array($data);
                    }
                }
                else if ($paramClass = $param->getClass()){
                    // Not an accidental assignment above.
                    $data = $this->request->converter()->convertAs($paramClass);
//                    $routeData = $this->route->getRouteDataArray();
//                    if ($routeData != null && count($routeData) > 0){
//                        foreach ($routeData as $key => $value) {
//                            if (property_exists($data, $key)){
//                                $data->{$key} = $value;
//                            }
//                        }
//                    }

                    if ($data instanceof BaseModel)
                    {
                        $data->validate();
                    }
                }
                else{
                    if ($this->request->hasKey($param->name)){
                        $data = $this->request->param($param->name);
                    }
                    else{
                        $data = $this->route->getRouteData($param->name);
                    }
                }

                if ($param->isPassedByReference()){
                    $arguments[$param->name]= &$data;
                }
                else{
                    $arguments[$param->name] = $data;
                }
            }

            $this->beforeMethodCall($methodContext);
            
            if ($methodContext->response === null){
                $returned = $method->invokeArgs($this, count($arguments > 0) ? $arguments : null);
                if (isset($returned)){
                    if ($returned instanceof BaseApiResponse){
                        $methodContext->response = $returned;
                    }
                    else{
                        $methodContext->response = $this->ok($returned);
                    }
                }
                else{
                    $methodContext->response = $this->ok();
                }
            }
        }
        catch(\BadMethodCallException $ex){
            $methodContext->response = $this->methodNotAllowed($ex->getMessage());
        }
        catch (\Exception $ex) {
            $methodContext->response = $this->internalServerError($ex);
        }

        $this->response->flushHeaders();
        
        if ($methodContext->response !== null) {    
            $methodContext->response->process();
        }
        
        return true;
    }
    
    /**
     * Gets an object that contains HTTP request data.
     * @return \Amvisie\Core\HttpRequest A request object.
     */
    public function &request() : \Amvisie\Core\HttpRequest
    {
        return $this->request;
    }
    
    /**
     * Gets an object that handles HTTP response data.
     * @return \Amvisie\Core\HttpResponse A response object.
     */
    public function &response() : \Amvisie\Core\HttpResponse
    {
        return $this->response;
    }

    /**
     * Gets a reference to Route class that contains parsed url.
     * @return Route
     */
     public function &getRoute() : \Amvisie\Core\Route
     {
        return $this->route;
    }

    /**
     * Sets a reference to Route class.
     * @param Route $router
     */
    public function setRoute(\Amvisie\Core\Route &$route) 
    {
        $this->route = $route;
    }

    /**
     * Invoked before call of requested method.
     * @param \Amvisie\Core\MethodContext $context
     */
    protected function beforeMethodCall(MethodContext $context){
        // Acting as a placeholder virtual method. Cannot make it abstract as it is not compulsory to implement in derived classes.
    }
    
    protected function internalServerError(\Exception $exception){
        return ResponseFactory::create($this, array(
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'status' => 500
        ), 500);
    }
    
    protected function ok($content = null){
        return ResponseFactory::create($this, $content);
    }
    
    protected function notFound() {
        return ResponseFactory::create($this, null, 404);
    }
    
    protected function unauthorized($content = null)
    {
        return ResponseFactory::create($this, $this->getHttpContent(401, $content), 401);
    }
    
    protected function forbidden($content = null)
    {
        return ResponseFactory::create($this, $this->getHttpContent(403, $content), 403);
    }

    private function methodNotAllowed($message){
        return ResponseFactory::create($this, $this->getHttpContent(405, $message), 405);
    }
    
    private function getHttpContent($status, $content = null){
        if (!isset($content)){
            return [
                'status' => $status,
                'message' => BaseApiResponse::getStatusText($status)
            ];
        }
        else if (is_array($content) || is_object($content)){
            return [
                'status' => $status,
                'data' => $content,
                'message' => BaseApiResponse::getStatusText(401)
            ];
        }
        else{
            return [
                'status' => $status,
                'message' => $content
            ];
        }
    }
    
    /**
     * Identifies the method to invoke.
     * A route may not have @method in defaults array. BaseApiController attempts to find the name of method 
     * based on HTTP method. Such as GET request with @id data suggests to look for get($id) method in derived controller class.
     * If suggested method isn't found, 405 response is issued.
     * When a route consists of @method in defaults, priority is given to that method, but still this method must be prefixed with
     * HTTP method, such as getImage().
     * 
     * Whatever available in url is passed as direct values in parameter.
     * Whatever available in BODY is only transformed as object.
     * 
     * GET /api/controller/@id/@name & /api/controller/@id?name=@name
     * The values can only be passed as parameters of specific types. Url values cannot be transformed into objects.
     * get($id, $name)
     * 
     * POST /api/controller/@id
     * BODY name=@name&standard=@standard 
     * Id is passed to $id parameter and Name & Standard are passed as object in $object
     * post($id, $object)
     * 
     * URL Data is always passed to parameters irrespective of http method.
     * 
     */
    private function getFinalMethod(){
        $routeMethod = $this->route->getMethod();
        
        // $prefix is HTTP method: GET, POST, PUT or DELETE.
        $httpMethod = $this->request->getMethod();
        
        $urlData = array_merge($this->request->getArray(), $this->route->getRouteDataArray());
        
        // Collects all methods that starts with HTTP method.
        $matchedMethod = array();
        
        if (count($routeMethod) == 0){
            // Default method is not available in Route collection. Now guess the method based solely on HTTP method.
            
            $matches = 0;
            $first = true;
            
            /* @var $method \ReflectionMethod */
            foreach ($this->reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
            {
                if ($this->reflector->name !== $method->class){
                    break;
                }
                
                if ($method->isConstructor() || $method->isStatic() || stripos($method->name, $httpMethod) !== 0){
                    continue;
                }
                
                $params = array();
                
                /* @var $method \ReflectionParameter */
                foreach($method->getParameters() as $parameter){
                    $parameterClass = $parameter->getClass();
                    if ($parameterClass !== null){
                        continue;
                    }
                    
                    if (array_key_exists($parameter->name, $urlData)){
                        $matches++;
                        $params[$parameter->name] = $urlData[$parameter->name];
                    }
                }
                
                if ($method->getNumberOfRequiredParameters() === 0 && count($urlData) === 0)
                {
                    $matches++; 
                }
                
                if ($first || $matches > $matchedMethod['paramCount']){
                    $matchedMethod['name'] = $method;
                    $matchedMethod['parameters'] = $params;
                    $matchedMethod['paramCount'] = $matches;
                    $first = false;
                }
                else if ($matches === $matchedMethod['paramCount']){
                    throw new \Exception('Multiple ' . $httpMethod . ' methods are found in ' . $this->reflector->name);
                }
                
                $matches = 0;
            }
        }
        else{
            // Default method is available in Route collection.
            $method = $this->request->getMethod() . $routeMethod;
            
            if ($this->reflector->hasMethod($method)){
                $matchedMethod['name'] = $this->reflector->getMethod($method);
            }
        }
        
        if (count($matchedMethod) == 0){
            // No method in current controller starts with HTTP method.
            throw new \BadMethodCallException($httpMethod);
        }
        
        return $matchedMethod;
    }
    
    private function getTypeFromPhpDoc($comment, $paramName){
        if ($comment === false){
            return null;
        }
        
        $pattern = '#@param\s+(?:([\\\]{0,1}[a-zA-Z][a-zA-Z0-9\\\]+[\[\]]+)\s+\$([_a-zA-Z][a-zA-Z0-9]*))#';
        $matches = [];
        
        preg_match_all($pattern, $comment, $matches);
        
        if (count($matches[0]) === 0){
            return null;
        }
        
        $objects = $matches[1];
        
        $index = array_search($paramName, $matches[2]);
        
        if ($index === false){
            return null;
        }
        
        $objectType = $matches[1][$index];
        
        return new \ReflectionClass(substr($objectType, 0, stripos($objectType, '[]')));
    }
}