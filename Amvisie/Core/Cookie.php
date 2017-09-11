<?php
declare(strict_types=1);

namespace Amvisie\Core;

/**
 * Acts as a container for cookie.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class Cookie
{
    private $values = [];

    private $name;

    private $value = '';

    public  $path = '/',

    /**
     * Whether cookie should be restricted to http only.
     * @var bool Default false
     */
    $httpOnly = false,

    $domain  = '',

    /**
     * Whether the cookie must be transferred over HTTPS only.
     * @var bool Default false.
     */
    $secure = false,

    /**
     * A unix timestamp value. Default is 0.
     * @var int A timestamp value.
     */
    $expires = 0;

    public function __construct(string $name, int $expires = 0, bool $httpOnly = false)
    {
        $this->name = $name;
        $this->expires = $expires;
        $this->httpOnly = $httpOnly;
    }

    public function setValue(string $value) : void
    {
        $this->value = $value;
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function add(string $key, string $value) : void
    {
        $this->values[$key] = $value;
    }

    public function get(string $key) : ?string
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : null;
    }


    /**
     * Writes cookies in header.
     * @internal Infrastructure method. Do not call.
     */
    public function flush() : void
    {
        $values = strlen($this->value) > 0 ? $this->value . '&' : '';

        foreach ($this->values as $key => $value) {
            $values .= $key . '=' . $value . '&';
        }

        setrawcookie(
                $this->name,
                rtrim($values, '&'),
                $this->expires,
                $this->path,
                $this->domain,
                $this->secure,
                $this->httpOnly
                );
    }

    /**
     * Reads cookie data from request.
     * @internal Infrastructure method. Do not call.
     */
    public function parse($value) : void
    {
        $this->value = $value;

        $split = explode('&', $this->value);
        foreach ($split as $pair) {
            $firstEqual = strpos($pair, '=');
            if ($firstEqual === false) {
                $this->values[0] = $pair;
                continue;
            }

            $this->values[substr($pair, 0, $firstEqual)] = substr($pair, $firstEqual + 1);
        }
    }
}
