<?php
declare(strict_types=1);

namespace Amvisie\Core;

/**
 * An interface as a contract for BaseController and BaseApiController.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
interface ControllerInterface 
{
    function invoke() : bool;

    function &getRoute() : \Amvisie\Core\Route;

    function &request() : \Amvisie\Core\HttpRequest;
}
