<?php
declare(strict_types=1);

namespace Amvisie\Core;

/**
 * Extracts controller, method, and route data from requested URL.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
final class Route
{
    CONST CONTROLLER_DIR = 'Controllers/';
    CONST CONTOLLER_SUFFIX = 'Controller';

    /* -- Static Members Section -- */

    /**
     * A static instance the collects various route configurations. Each route contains
     * a pattern url, defaults (array), regexes (array), name of directory, and array of separators.
     * @var array
     */
    private static $routes = array();

    /**
     * A regular expression to parse name of controller from url.
     * @var string
     */
    private static $controllerRegEx = '[a-z0-9]*';

    /**
     * A regular expression to parse name of method from url.
     * @var string
     */
    private static $methodRegEx = '[a-z0-9]*';

    /**
     * A regular expression to parse @id from url.
     * @var string
     */
    private static $idRegEx = '\w*';

    
    /**
     * Adds a route pattern with defaults and regular expressions.
     * @param string $name A unique name of pattern. A name of default pattern is always ROOT. It can be overriden.
     * @param string $url A url pattern. It can have predefined pattern variables, such as @controller, @method, and @id.
     * However, a pattern may also contain user variables prefixed with @ symbol.
     * @param Array $defaults An array containing name of default Controller, and Method.
     * @param Array $regex An array containing regex patterns for user variables available in URL pattern.
     * @param string $directory A name of directory if the controllers are not available in application root. Default is null.
     * @param Array $separators An array containing characters which are used to separate sections in URL patterns.
     */

    public static function addRoute (
            string $name,
            string $url,
            array $defaults = array(),
            array $regex = array(),
            string $directory = null,
            array $separators = null
        ) : void {
        $array = array('ROUTE' => $url, 'DEFAULT' => $defaults, 'REGEX' => $regex == null ? array() : $regex);
        if ($directory == null || trim($directory) == '') {
            $directory = '';
        }

        $array['DIR'] = $directory == null ? '' : trim($directory);

        if ($separators == null) {
            $separators = array('/');
        }

        $array['SEP'] = $separators;

        self::$routes[$name] = &$array;
    }
    
    public static function parse(string $requestUri, string $alias = '') : ?Route
    {
        // Checks if ROOT default is added by user. If not, define a default.
        if (!array_key_exists('ROOT', self::$routes)) {
            self::addRoute('ROOT', '@controller/@method/@id', array('@controller' => 'home', '@method' => 'index', '@id' => null));
        }
        
        try {
            return new Route($requestUri, $alias);
        } catch (\Exception $ex) {
            return null;;
        }
    }

    /* -- Instance Members Section -- */

    /**
     * A name of controller.
     * @var string
     */
    private $controller;

    /**
     * A directory where controller is located.
     * @var string
     */
    private $controllerDir;

    /**
     * A name of method of controller.
     * @var string
     */
    private $method;

    /**
     * An array that contains current route data.
     * @var array
     */
    private $routeData = [];

    /**
     * A route url.
     * @var string
     */
    private $routeUrl;

    /**
     * A path of directory where request path is mapped.
     * @var string
     */
    private $requestDirectory;

    /**
     * Name of controller file.
     * @var string
     */
    private $controllerFile;

    /**
     * An array that contains current route information.
     * @var array
     */
    private $currentRoute;

    private function __construct(string $requestUri, string $alias = '')
    {
        $this->setRouteUrl($requestUri, $alias);

        $this->prepare();

        if ($this->controller == null) {
            // A pattern seems to be invalid.
            throw new \Exception("Url doesn't match with any route added in route collection.");
        }
    }
    
    /**
     * Gets a physical path of requested directory.
     * @return string A path of directory.
     */
    public function getDirectory() : string
    {
        return $this->requestDirectory;
    }

    /*public function &getViewDirectories() : array{
        return $this->viewDirs;
    }*/

    /**
     * Gets a name of controller with or without 'Controller' suffix.
     * @param bool $noSuffix [optional] A boolean value to exclude suffix from name.
     * @return string A name of controller if it is found in URI or route defaults; otherwise null.
     */
    public function getController(bool $noSuffix = false) : string
    {
        if ($noSuffix){
            return $this->controller;
        } else{
            return $this->controller . self::CONTOLLER_SUFFIX;
        }
    }

    /**
     * Gets a path of controller file. Make sure that the file exists before using it.
     * @return string A name of controller file if controller is found in URI or route defaults; otherwise null;
     */
    public function getControllerFile() : string
    {
        return $this->controllerFile;
    }

    /**
     * Gets a name of method extracted from URI or route default.
     * @return string Name of method if found; otherwise null;
     */
    public function getMethod() : ?string
    {
        return $this->method;
    }

    /**
     * Gets an array of data available in route URI.
     * @return Array A key-value pair array containing route data. A key defines name of variable.
     */
    public function getRouteData(string $key)
    {
        return array_key_exists($key, $this->routeData) ? $this->routeData[$key] : null;
    }

    public function &getRouteDataArray() : array
    {
        return $this->routeData;
    }

    public function getRouteUrl(string $method = null, string $controller = null, array $routeData = array()) : string
    {
        /**
         * Cases -
         * A. If DIR is not specified in $routeData, make following assumptions.
         * 1. If $controller is specified, use mapping of route's controller (default). Match default controller in
         * each route.
         * 2. If $controller is not specified, use current route.
         *
         * B. If DIR is '',
         * 1. But it does not have entry in route collection, use ROOT route only if DIR matches. Otherwise, create direct link.
         *
         * C. If DIR is not empty, find the directory in route collection. If not found, just create direct link.
         */

        $routeString = null;
        $routeSep = null;
        $url = '';
        $urlData = array();

        // Extract from current route.
        if ($method == null) {
            $method = $this->method;
        }

        if ($controller == null) {
            $controller = $this->controller;
        }

        $routeVars = null;
        if (!array_key_exists('DIR', $routeData)) {
            $routeString = $this->currentRoute['ROUTE'];
            $routeSep = implode('', $this->currentRoute['SEP']);
            $routeVars = preg_split('^' . implode('|', $this->currentRoute['SEP']) . '^', $routeString);
        } else if ($routeData['DIR'] == '') {
            $routeString = self::$routes['ROOT']['ROUTE'];
            $routeSep = implode('', self::$routes['ROOT']['SEP']);
            $routeVars = preg_split('^' . implode('|', self::$routes['ROOT']['SEP']) . '^', $routeString);
            unset($routeData['DIR']);
        } else {
            foreach (self::$routes as $key => $route) {
                if (strtolower($route['DIR']) == strtolower($routeData['DIR'])) {
                    $routeString = $route['ROUTE'];
                    $routeSep = implode('', $route['SEP']);
                    $routeVars = preg_split('^' . implode('|', $route['SEP']) . '^', $routeString);
                    unset($routeData['DIR']);
                    break;
                }
            }
        }
        
        if ($routeVars == null) {
            $url = $controller . '/' . $method;
        } else {
            // Find controller and method placeholder in route url and replace them with controller and method name respectively.
            $url = str_replace(array('@controller', '@method'), array($controller, $method == 'index' ? '' : $method), $routeString);

            foreach ($routeVars as $key) {
                if ($key[0] != '@') { 
                    continue; 
                }

                $var = substr($key, 1);
                if (array_key_exists($var, $routeData)){
                    $url = str_replace($key, $routeData[$var], $url);
                    unset($routeData[$var]);
                } else {
                    $url = str_replace($key, '', $url);
                }
            }

            $url = trim($url, ' ' . $routeSep);
        }

        foreach ($routeData as $key => $value) {
            $urlData[] = $key . '=' . $value;
        }

        if (count($urlData) > 0)
        {
            $url .= '?' . implode ('&', $urlData);
        }
        
        return ALIAS . $url;
    }

    /**
     * Sets an appropriate controller file and name in respective properties.
     * @param string $controllerName A name of controller parsed from url or default name from route collection.
     * @throws \Exception When there are more than one controller file found with same name.
     */
    private function setController(string $controllerName) : void
    {
        // Prepares path of directory of a controller file.
        $this->controllerDir = $this->requestDirectory . self::CONTROLLER_DIR;
            
        // Find a controller file in Controllers directory.
        $directoryIterator = new \DirectoryIterator($this->controllerDir);

        // Make sure to make case insensitive match because the controller name in url may be formed in any case.
        $regexIterator = new \RegexIterator($directoryIterator, '#^' . $controllerName . 'controller.php$#i');

        $files = [];
        $fileCount = 0;
        $controller = '';

        foreach ($regexIterator as $iterator) {
            // Get name of Controller from file name assuming that name of controller and file are same.
            $controller = $iterator->getBasename(self::CONTOLLER_SUFFIX . EXTN);

            // Get full path of controller file. Storing the name in array to notify the user that more than one controller file are found.
            $files[] = $iterator->getRealPath();

            // Increment found file counter.
            $fileCount++;
        }

        if ($fileCount > 1) {
            // More than one controller file have been found for matched url.
            throw new \Exception('More than one controller file have been found for "' .
                    $this->routeUrl . '\r\n\r\n' . implode('\n', $files));
        } else if ($fileCount === 1){
            $this->controller = $controller;
            $this->controllerFile = $files[0];
        }
    }
    
    private function setRouteUrl(string $requestUri, string $alias) : void
    {
        $aliasLength = strlen($alias);
        
        if ($aliasLength > 0 && substr($requestUri, 0, $aliasLength) === ALIAS) {
            $requestUri = substr($requestUri, $aliasLength);
        }

        if (!empty($requestUri)) {
            $index = strpos($requestUri, '?');
            if ($index !== false) {
                $this->routeUrl = substr($requestUri, 0, $index);
            } else {
                $this->routeUrl = $requestUri;
            }
        }
        
        $this->routeUrl = $this->routeUrl ? trim($this->routeUrl, '/') : '/';
    }

    /**
     * A private member that extracts the URL into meaningful data.
     */
    private function prepare() : void
    {
        // Iterates through each ROUTE configuration and matches URL with specified route.
        foreach (self::$routes as $key => $value) {
            $routeEx = $this->buildExpression($value);
            
            // Matches prepared regex with route URI.
            $matchResult = preg_match($routeEx, $this->routeUrl);
            
            if ($matchResult === 0) {
                continue;
            }
            
            // The match is found. Now extract controller, method, and other route data.
            $this->currentRoute = &$value;

            $splitGlue = '@' . implode('|', $value['SEP']) . '@';   // Creates a glue that joins separators.
            $urlParts = preg_split($splitGlue, $this->routeUrl);    // Splits the URI by separators.

            $controllerName = '';

            $matchedRouteParts = preg_split($splitGlue, $value['ROUTE']);   // Splits the route pattern.

            // Iterates through each element of route pattern.
            for ($index = 0; $index < count($matchedRouteParts); $index++) {
                // $urlParts is supposed to be in line with $matchedRouteParts.
                // Such as @controller/@method/@id. But URI may not have name of method and id.
                if (!isset($urlParts[$index])) {
                    // Discontinues the execution of loop.
                    break;
                }

                // Checks if current route part starts with @ symbol. It may be @controller, @method,
                // @id, or any other user defined variable.
                if ($matchedRouteParts[$index][0] === '@') {
                    if ($matchedRouteParts[$index] === '@controller') {
                        $controllerName = strtolower($urlParts[$index]);
                    } else if ($matchedRouteParts[$index] === '@method') {
                        $this->method = strtolower($urlParts[$index]);
                    } else {
                        // Prepare $routeData array with variable data available in URI.
                        $this->routeData[substr($matchedRouteParts[$index], 1)] = $urlParts[$index];
                    }
                }
            }

            if (empty($this->method) && isset($value['DEFAULT']['@method'])) {
                // Method is not available in URI, extract method from default.
                $this->method = $value['DEFAULT']['@method'];
            }

            if (empty($controllerName)) {
                // Controller is not available in URI, extract controller from default.
                $controllerName = $value['DEFAULT']['@controller'];
            }

            // Each controller class and its file is suffixed with 'Controller'. Concatenate it with name of controller.
            // $this->controller .= 'Controller';
            // Prepares path of requested directory.
            $this->requestDirectory = 
                    APP_PATH . (isset($value['DIR']) && !empty($value['DIR'])  ? $value['DIR'] . '/' : '');

            $this->setController($controllerName);
            
            /*if(defined('VIEW_DIR')) {
                // VIEW_DIR might not be defined in case of Web Apis.
                $this->viewDirs[] = $this->requestDirectory . VIEW_DIR . $this->controller . '/';
                $this->viewDirs[] = $this->requestDirectory . VIEW_DIR . 'Shared/';
                if(!empty(self::$routes['ROOT']['DIR'])){
                    $this->viewDirs[] = APP_PATH . self::$routes['ROOT']['DIR'] . VIEW_DIR . 'Shared/';
                }
            }*/
            // Exits from outer FOREACH loop since there is no need to continue pattern matching.

            break;
        }
    }
    
    /**
     * Builds regular expression based on route.
     * @param array $value A route array.
     * @return string A regular expression.
     */
    private function buildExpression(array $value) : string
    {
        // In following steps, prepare a regex pattern to match with requested URI. 
        // Each variable that starts with @ symbol is replaced with appropriate regular expression.
        $routeEx = str_replace('@controller', self::$controllerRegEx, $value['ROUTE']);
        $routeEx = str_replace('@method', self::$methodRegEx, $routeEx);


        foreach ($value['SEP'] as $val) {
            $routeEx = str_replace($val, $val . '*', $routeEx);
        }

        if (array_key_exists('@id', $value['REGEX'])) {
            $routeEx = str_replace('@id', $value['@id'], $routeEx);
        } else {
            $routeEx = str_replace('@id', self::$idRegEx, $routeEx);
        }

        // Iterates through regexes defined by user.
        foreach ($value['REGEX'] as $key => $val) {
            $routeEx = str_replace($key, $val, $routeEx);
        }

        $routeEx = '#^' . $routeEx . '$#i';
        
        return $routeEx;
    }
}
