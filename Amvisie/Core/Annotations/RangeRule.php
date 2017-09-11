<?php

namespace Amvisie\Core\Annotations;

use Amvisie\Core\BaseRule;

/**
 * Enforces a rule of value to exist between the specified range..
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class RangeRule extends BaseRule
{
    private $minValue;
    private $maxValue;

    public function __construct(string $message, $maxValue, $minValue)
    {
        parent::__construct($message);
        
        $this->maxValue = $maxValue;
        $this->minValue = $minValue;
    }
    
    public function getHtmlAttributes() : array
    {
        return array('data-min' => $this->message,
            'data-max' => $this->patternJS);
    }

    public function validate(\Amvisie\Core\BaseModel &$model, string $property) : bool
    {
        $value = $model->{$property};
        return $value == '' || ($value <= $this->maxValue && ($this->minValue == null ? true : $value >= $this->minValue ));
    }
}
