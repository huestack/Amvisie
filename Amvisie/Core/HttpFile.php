<?php

namespace Amvisie\Core;

final class HttpFile
{
    /**
     * @var string A name of file.
     */
    private $name;
    
    /**
     * @var string Content type of file.
     */
    private $type;
    
    /**
     * @var string A path of file where is temporarily located.
     */
    private $tempPath;
    
    /**
     * @var int Total size of file in bytes.
     */
    private $size = 0;
    
    /**
     * @var string And error message if any error occured.
     */
    private $error;
    
    public function __construct(string $name = '', string $type = '')
    {
        $this->name = $name;
        $this->type = $type;
    }
    
    public function setName(string $value) : void
    {
        $this->name = $value;
    }
   
    public function getName() : string
    {
        return $this->name;
    }
    
    public function setType(string $value) : void
    {
        $this->type = $value;
    }
   
    public function getType() : string
    {
        return $this->type;
    }
    
    public function setTempPath(?string $path) : void
    {
        $this->tempPath = $path;
    }
   
    public function getTempPath() : ?string
    {
        return $this->tempPath;
    }
    
    public function setSize(int $size) : void
    {
        $this->size = $size;
    }
   
    public function getSize() : int
    {
        return $this->size;
    }
    
    public function setError(?string $message) : void
    {
        $this->error = $message;
    }
   
    public function getError() : ?string
    {
        return $this->error;
    }
}
