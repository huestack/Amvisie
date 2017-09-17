<?php
declare(strict_types=1);

namespace Amvisie\Core;

/**
 * Encapsulates HTTP request information.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class HttpRequest
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_PUT = 'put';
    const METHOD_DELETE = 'delete';
    const METHOD_PATCH = 'patch';
    
    /**
     * Represents if current request is made by AJAX call.
     * @var bool
     */
    private $isAjax;
    
    /**
     * A method that is used by client to make a request.
     * @var string GET, POST, DELETE, PUT etc.
     */
    private $method;
    
    /**
     * A collective array data of both GET and POST methods.
     * @var array
     */
    private $data = array();

    /**
     * MIME type of data available in request body.
     * @var string 
     */
    private $contentType;
    
    /**
     * An array of files got from POST/PUT request with multipart/form-data Content-type.
     * @var array 
     */
    private $filesArray = null;
    
    /**
     * A list of http headers.
     * @var array 
     */
    private $headers = null;

    /**
     * A list of GET data.
     * @var array 
     */
     private $getArray = null;

     /**
     * A list of GET data.
     * @var array 
     */
     private $postArray = null;
    
    /**
     * A list of cookies fetched from client.
     * @var array 
     */
    private $cookies = [];
    
    /**
     *
     * @var RequestConverters\BaseConverter 
     */
    private $converter;

    public function __construct()
    {     
        $this->method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
        
        $this->parseHeaders();
        
        $this->parseCookies();
        
        $this->setContentType();
        
        $factory = new RequestConverters\ConverterFactory($this);
        $this->converter = $factory->build();
        
        $this->parseData();
    }
    
    /**
     * Gets a data from query string.
     * @param mixed $key A key name.
     * @return mixed A string or an array.
     */
    public function get(string $key)
    {
        return $this->getArray[$key];
    }
    
    /**
     * Gets a data from HTTP body.
     * @param mixed $key A key name.
     * @return mixed A string or an array.
     */
    public function post(string $key)
    {
        return filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    /**
     * Gets a data from HTTP query string or body.
     * @param string $key A key name.
     * @return mixed A string or an array.
     */
    public function param(string $key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : '';
    }
    
    
    /**
     * Gets cookie data in a container.
     * @param string $key A name of cookie.
     * @return \Amvisie\Core\Cookie An instance of Cookie class that acts as a container for cookie data.
     */
    public function cookie(string $key)
    {
        return array_key_exists($key, $this->cookies) ? 
                $this->cookies[$key] : null;
    }
    
    /**
     * Gets HTTP method i.e. get, post.
     * @return string A lowercase method name.
     */
    public function getMethod() : string
    {
        return $this->method;
    }
    
    public function getAccept() : string
    {
        return filter_input(INPUT_SERVER, 'HTTP_ACCEPT');
    }
    
    /**
     * Gets request data from $_GET, $_POST, and $_COOKIE.
     * @return array An array of request data.
     */
    public function &getArray() : array
    {
        return $this->data;
    }
    
    /**
     * Gets an array of files.
     * @param string $key
     * @return array
     */
    public function &getFiles() : ?array
    {
        return  $this->filesArray;
    }

    /**
     * Gets an object of HttpFile class or array of objects of HttpFile class.
     * @return array|HttpFile
     */
     public function &getFile(string $key)
     {
        return  $this->filesArray[$key];
    }
    
    public function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }
    
    /**
     * Gets MIME type of content available in HTTP request body.
     * @return string e.g application/json.
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    
    /**
     * Gets a boolean value indicating if HTTP content type is application/json. Simplified for Web Api.
     * @return boolean true/false.
     */
    public function isBodyJson()
    {
        return $this->contentType === 'application/json';
    }
    
    /**
     * Gets a boolean value indicating if HTTP content type is multipart/form-data. Simplified for Web Api.
     * @return boolean true/false.
     */
    public function isBodyFormData()
    {
        return $this->contentType === 'multipart/form-data';
    }
    
    /**
     * Gets a boolean value indicating if HTTP content type is application/x-www-form-url-encoded. Simplified for Web Api.
     * @return boolean true/false.
     */
    public function isBodyUrlEncoded()
    {
        return $this->contentType === 'application/x-www-form-urlencoded';
    }

    /**
     * Determines whether current request is generated by AJAX call.
     * @return bool 
     */
    public function isAjax()
    {
        return $this->isAjax;
    }
    
    /**
     * Gets a requested url.
     * @return string A current url.
     */
    public function getUrl()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_URI');
    }
    
    /**
     * Gets a header content from http request.The key is case insensitive.
     * @param string $key A request header key.
     * @return string A value from http header. If the key is invalid, false is returned.
     */
    public function header(string $key) : ?string
    {
        $lowerKey = strtolower($key);
        
        return $this->headers[$lowerKey] ?? '';
    }
    
    public function &converter() : ?RequestConverters\BaseConverter
    {
        return $this->converter;
    }

    private function setContentType() : void
    {
        $type = $this->header('content-type');
        if ($type === false || $type === null) {
            $this->contentType = 'text/plain';
        } else if (strpos($type, ';')) {
            $array = explode(';', $type);
            $this->contentType = trim($array[0]);
        } else {
            $this->contentType = trim($type);
        }
    }
    
    private function parseData() : void
    {
        $this->getArray = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);
        
        if ($this->method !== 'get') {    
            $this->postArray = $this->converter ? $this->converter->getData() : filter_input_array(INPUT_POST);

            // A converter is available. Make sure that it generates an array to use by request object.
            $this->data = array_merge($this->getArray ?? [], $this->postArray ?? []);
            $this->filesArray = $this->converter ? $this->converter->getFiles() : $_FILES;
        } else {
            $this->data = array_merge($this->getArray ?? [], $this->postArray ?? []);
        }
        
        // Had to set it when debugger in attached in netbeans.
        unset($this->data['XDEBUG_SESSION_START']);
        
//        foreach ($this->data as $key => $value) {
//            $this->data[$key] = htmlspecialchars($value);
//        }
    }
    
    /**
     * Parses all http request header into an array to get easy access without using $_SERVER.
     * @author Ralph Khattar <https://github.com/ralouphie/getallheaders>
     */
    private function parseHeaders() : void
    {
        $this->headers = array();
        
        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );
        
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', strtolower(str_replace('_', ' ', $key)));
                    $this->headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $this->headers[strtolower($copy_server[$key])] = $value;
            }
        }
        
        if (!isset($this->headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $this->headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $this->headers['authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $this->headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }
        
        $this->isAjax = strtolower($this->header('x-requested-with') ?? '') === 'xmlhttprequest';
    }
    
    /**
     * Parses cookie data into \Amvisie\Core\Cookie container.
     */
    private function parseCookies() : void
    {
        $cookies = filter_input_array(INPUT_COOKIE);
        if (!isset($cookies)) {
            return;
        }
        
        foreach($cookies as $key => $value){
            $cookie = new Cookie($key);
            $cookie->parse($value);
            
            $this->cookies[$key] = $cookie;
        }
    }
}
