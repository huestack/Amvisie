<?php

namespace Amvisie\Core\Annotations;

use Amvisie\Core\BaseRule;

/**
 * Enforces the comparison of two properties to have same value.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class EqualToRule extends BaseRule
{
    private $equalToProperty;
    
    public function __construct(string $message, string $equalToProperty)
    {
        parent::__construct($message);
        
        $this->equalToProperty = $equalToProperty;
    }

    public function getHtmlAttributes() : array
    {
        return array('data-msg-equalto' => $this->message, 'data-rule-equalto' => '#' . $this->equalToProperty);
    }

    public function validate(\Amvisie\Core\BaseModel &$model, string $property) : bool
    {
        $errors = $model->getErrors();
        if (isset($errors[$this->equalToProperty])) {
            // It means the comparable property has already generated an error.
            return true; 
        } else {
            return $model->{$this->equalToProperty} == $model->{$property};
        }
    }    
}
