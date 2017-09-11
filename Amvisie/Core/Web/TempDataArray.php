<?php
namespace Amvisie\Core\Web;

/**
 * An implementation of \ArrayAccess to store values or objects to travel between two subsequent requests.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class TempDataArray implements \ArrayAccess
{
    public function offsetExists($offset)
    {
        return UserSession::hasKey('TempData-' . $offset);
    }

    public function offsetGet($offset)
    {
        $object = UserSession::getObject('TempData-' . $offset);
        
        UserSession::remove('TempData-' . $offset);
        
        return $object;
    }

    public function offsetSet($offset, $value) : void
    {
        UserSession::setObject('TempData-' . $offset, $value);
    }

    public function offsetUnset($offset) : void
    {
        UserSession::remove('TempData-' . $offset);
    }
}
