<?php

namespace Amvisie\Core;

/**
 * Description of HttpResponse
 *
 * @author Ritesh
 */
final class HttpResponse
{
    private $cookies = [];
    private $headers = [];
    private $httpHeader = '';


    public function setHeader(string $header, string $value) : void
    {
        $this->headers[$header] = $value;
    }
    
    public function setCookie(Cookie $cookie) : void
    {
        $this->cookies[] = $cookie;
    }
    
    public function setHttpHeader(int $code, string $message) : void
    {
        $this->httpHeader = 'HTTP/1.1 ' . $code . ' ' . $message;
    }
    
    /**
     * Flushes headers to client.
     * @internal Infrastructure Method. Do not call.
     */
    public function flushHeaders() : void
    {
        if (!empty($this->httpHeader)) {
            header($this->httpHeader);
        }
        
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }
        
        foreach ($this->cookies as $cookie) {
            $cookie->flush();
        }
    }
    
    public function text(string $format, string ...$str) : void
    {
        echo \sprintf($format, ...$str);
    }
    
    public function json($object) : void
    {
        echo json_encode($object);
    }

    public function end() : void
    {
        flush();
        exit();
    }
}
