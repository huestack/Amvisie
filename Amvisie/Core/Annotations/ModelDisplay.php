<?php

namespace Amvisie\Core\Annotations;

/**
 * Represents a display information about model properties.
 * @author Ritesh Gite
 */
class ModelDisplay
{
    private $caption, $description;
    
    public function __construct(string $caption, string $description = null)
    {
        $this->caption = $caption;
        $this->description = $description;
    }
    
    /**
     * Gets a caption value of model property.
     * @return string A caption.
     */
    public function getCaption() : string
    {
        return $this->caption;
    }
    
    /**
     * Gets a description value of model property.
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }
}
