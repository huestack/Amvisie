<?php

namespace Amvisie\Core\Api;

/**
 * Decides the type of response to generate based on HTTP Accept header.
 *
 * @author Ritesh Gite <huestack@yahoo.com>
 */
final class ResponseFactory {
    
    private static $types = array(
        'application/json' => JsonResponse::class
    );

    /**
     * Creates an instance of a sub class of \Amvisie\Core\Api\BaseApiResponse object based on the value in Accept header in HTTP.
     * @param \Amvisie\Core\ControllerInterface $controller
     * @param mixed $content
     * @param int $statusCode A HTTP status code. Default is 200.
     * @return \Amvisie\Core\Api\BaseApiResponse
     */
    public static function create(\Amvisie\Core\ControllerInterface $controller, $content = null, int $statusCode = 200) : \Amvisie\Core\Api\BaseApiResponse
    {
        $contentType = $controller->request()->getAccept();

        if (array_key_exists($contentType, self::$types)) {
            $class =  self::$types[$contentType];

            $response = new $class($content);
            $response->setStatus($statusCode);
        } else {
            $response = new TextResponse("$contentType is not supported.");
            $response->setStatus(406);
        }
        
        return $response;
    }
    
    /**
     * Adds a user-defined handler class for given content type. The handler class must be derived from \Amvisie\Core\Api\BaseApiResponse.
     * @param type $contentType
     * @param type $handler
     */
    public static function addType($contentType, $handler) : void
    {
        self::$types[$contentType] = $handler;
    }
}
