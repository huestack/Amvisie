<?php

namespace Amvisie\Core;

/**
 * This factory resolves a controller from specified name.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
final class ControllerFactory implements ControllerFactoryInterface
{
    public function createController(string $controllerName) : ControllerInterface
    {
        $class = new \ReflectionClass($controllerName);
        
        return $class->newInstance();
    }
}
