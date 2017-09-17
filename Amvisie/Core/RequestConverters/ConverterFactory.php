<?php
declare(strict_types=1);

namespace Amvisie\Core\RequestConverters;

/**
 * Creates a converter which parses request data.
 * @author Ritesh Gite<huestack@yahoo.com>
 */
final class ConverterFactory
{
    /**
     * A request object.
     * @var \Amvisie\Core\HttpRequest 
     */
    private $request;
    
    public function __construct(\Amvisie\Core\HttpRequest &$request) {
        $this->request = $request;
    }
    
    public function build() : ?BaseConverter
    {
        $accept = $this->request->getContentType();
        
        if (array_key_exists($accept, self::$converters)) {
            /* @var $class \ReflectionClass */
            $class = new \ReflectionClass(self::$converters[$accept]);
            $expectedParent = \Amvisie\Core\RequestConverters\BaseConverter::class;
            
            if (!$class->isSubclassOf($expectedParent)) {
                throw new \Exception($class->name . ' is not implemented from ' . $expectedParent);
            }
            
            /*@var $instance BaseConverter */
            $instance = $class->newInstance();
            $instance->setHttpMethod($this->request->getMethod());
            if (!$instance->parse()) {
                throw new ConverterException("Parse error: " . $class->name . " cannot parse '" . $accept . "' data.");
            }
            
            return $instance;
        } else {
            return null;
        }
    }
    
    /**
     * An array of converters associated with content type.
     * @var array A key-value pair array.
     */
    private static $converters = array(
        'application/x-www-form-urlencoded' => \Amvisie\Core\RequestConverters\UrlEncodedConverter::class,
        'multipart/form-data' => \Amvisie\Core\RequestConverters\MultipartFormDataConverter::class,
        'application/json' => \Amvisie\Core\RequestConverters\JsonConverter::class
    );
    
    /**
     * Adds a user-defined handler class for given content type. The handler class must be derived from \Amvisie\Core\RequestConverters\BaseConverter class.
     * @param string $contentType Conte type such as application/json, application/xml etc.
     * @param string $handler A name of class that parses http stream data according to specified content type.
     */
    public static function addType(string $contentType, string $handler) : void
    {
        if (strlen(trim($handler)) == 0) {
            throw new InvalidArgumentException('$handler cannot be empty.');
        }
        
        self::$converters[$contentType] = $handler;
    }
}
