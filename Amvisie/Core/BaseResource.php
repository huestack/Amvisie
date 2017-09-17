<?php
declare(strict_types=1);

namespace Amvisie\Core;

/**
 * Provides an array to populate string resources.
 * @example \Amvisie\Core\BaseResource::get('emailCaption'). emailCaption is supposed to be a key set in derived class.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
abstract class BaseResource
{
    /**
     * An array containing key-value pairs of resources.
     * @var array 
     */
    private static $pairs = array();

    public static function init(\ReflectionClass $meClass) : void
    {
        $fileName = pathinfo($meClass->getFileName(), PATHINFO_FILENAME);
        $dirResource = dirname($meClass->getFileName());
        
        self::$pairs[static::class] = [];
        
        $resFile = $dirResource .'/' . $fileName . '.res' . EXTN;
        
        if (file_exists($resFile)) {
            include $resFile;
        }
        
        $culture = \Amvisie\Core\Culture::getCulture();

        if (!$culture->isEmpty()) {
            $resFile = $dirResource .'/' . $fileName . '.' . $culture->locale() . EXTN;
            if (file_exists($resFile)) {
                include $resFile;
            }
        }
    }
    
    /**
     * Gets a resource by identifier.
     * @param string $name Name of the identifier.
     * @return mixed A resource, usually a string, that is contained in collection.
     * @throws \InvalidArgumentException If $name parameter is null or not available in resource collection.
     */
    public static function get(string $name)
    {
        if (!isset(self::$pairs[static::class][$name])) {
            throw new \InvalidArgumentException("'$name' is either null or not defined in " . static::class);
        }
        
        return self::$pairs[static::class][$name];
    }
    
    public static function getAll() : array
    {
        return self::$pairs[static::class];
    }
    
    protected static function set(string $name, $value) : void
    {
        if (strlen($name) === 0) {
            throw new \InvalidArgumentException("'$name' is either null or not defined.");
        }
        self::$pairs[static::class][$name] = $value;
    }
    
    public static function __callStatic($name, $arguments) {
        if(!isset(self::$pairs[static::class][$name])){
            throw new \InvalidArgumentException("'$name' is either null or not defined in " . static::class);
        }
        return self::$pairs[static::class][$name];
    }
}
