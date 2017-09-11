<?php

namespace Amvisie\Core\Annotations;

use Amvisie\Core\BaseRule;

/**
 * Sometimes the requirement of value is dependent on value of another property. 
 * This class enforces the integrity of dependent values.
 * It uses data-msg-depends and data-rule-depends attributes for Javascript validation.
 * data-msg-depends contains an error message, while data-rule-depends contains a name of property. 
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class DependRequiredRule extends BaseRule
{
    
    private $dependsOnProperty;

    /**
     * Initializes DependRequiredRule.
     * @param string $message A message to display.
     * @param string $dependsOnProperty A name of property on which
     */
    public function __construct(string $message, string $dependsOnProperty)
    {
        parent::__construct($message);
        
        $this->dependsOnProperty = $dependsOnProperty;
    }
    
    public function getHtmlAttributes() : array
    {
        return array('data-msg-depends' => $this->message,
            'data-rule-depends' => $this->dependsOnProperty);
    }

    public function validate(\Amvisie\Core\BaseModel &$model, string $property) : bool
    {
        if ($model->{$this->dependsOnProperty} != null) {
            $value = $model->{$property};
            return !($value == null || trim($value) == '');
        } else {
            return true;
        }
    }
}
