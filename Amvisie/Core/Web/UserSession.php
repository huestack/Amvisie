<?php

namespace Amvisie\Core\Web;

session_start();

/**
 * Manages user's session data.
 * @author Ritesh Gite, <huestack@yahoo.com>
 */
class UserSession
{
    public static function init()
    {
        //session_start();
    }

    public static function setData($key, $value) : void
    {
        $_SESSION[$key] = $value;
    }
    
    public static function getData($key)
    {
        if (array_key_exists($key, $_SESSION))
            return $_SESSION[$key];
        else
            return null;
    }
    
    public static function setObject($key, $object)
    {
        $_SESSION[$key] = serialize ($object);
    }
    
    public static function getObject($key)
    {
        if (array_key_exists($key, $_SESSION))
            return unserialize ($_SESSION[$key]);
        else
            return null;
    }
    
    public static function remove($key)
    {
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy()
    {
        session_destroy();
    }
    
    public static function hasKey($key)
    {
        return array_key_exists($key, $_SESSION);
    }
    
    public static function getId()
    {
        return session_id();
    }
}
