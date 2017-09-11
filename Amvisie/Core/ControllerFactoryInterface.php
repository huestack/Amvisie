<?php

namespace Amvisie\Core;

/**
 *
 * @author ritesh
 */
interface ControllerFactoryInterface
{
    function createController(string $controllerName) : ControllerInterface;
}
