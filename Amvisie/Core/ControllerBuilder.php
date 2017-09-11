<?php
declare(strict_types=1);

namespace Amvisie\Core;

/**
 * Decides the type of controller (BaseController or BaseApiController) to instantiate.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
final class ControllerBuilder
{
    /**
     * @var \Amvisie\Core\ControllerFactoryInterface
     */
    private $factory;
    
    private function __construct()
    {
        
    }
    
    public function build() : ControllerInterface
    {
        $route = AppContext::getContext()->route();
        $controllerFile = $route->getControllerFile();
        
        if (isset($controllerFile)) {
            require $controllerFile;

            $controller = $route->getController();

            $class = self::getClassName($controllerFile);

            if (strtolower($class['class']) === strtolower($controller)){
                return $this->factory->createController(implode('\\', $class));
            } else {
                throw new \Exception("$controller could not be loaded.");
            }
        } else {
            throw new \Exception("$controllerFile does not exist.");
        }
    }
    
    public function setControllerFactory(ControllerFactoryInterface $factory) : void
    {
        $this->factory = $factory;
    }
    
    /**
     *
     * @var ControllerBuilder
     */
    private static $current;


    public static function &current() : ControllerBuilder
    {
        if (self::$current == null) {
            self::$current = new ControllerBuilder();
        }
        
        return self::$current;
    }
    
    /**
     * Gets a name of class from a file.
     * @param string $file A file name with full path to check name of first class available in it.
     * @return array An key-value pair array containing namespace and class names.
     * @author netcode, StackExchange user.
     * @link http://stackoverflow.com/a/7153391 A solution provided by <b>netcode</b>.
     */
    private static function getClassName(string $file) : array
    {
        $fp = fopen($file, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;

            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                             $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                             break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }

        return array('namespace' => $namespace, 'class' => $class);
    }
}
