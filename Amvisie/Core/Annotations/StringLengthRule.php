<?php
namespace Amvisie\Core\Annotations;

use Amvisie\Core\BaseRule;
/**
 * Enforces the property to have a string of mentioned length.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class StringLengthRule extends BaseRule
{

    private $maxLength, $minLength;

    public function __construct($message, $maxLength, $minLength = null)
    {
        parent::__construct($message);

        $this->maxLength = $maxLength;
        $this->minLength = $minLength == null || $minLength < 0 ? 0 : $minLength;
    }

    public function getHtmlAttributes() : array
    {
        $attrs = array('data-msg-maxlength' => sprintf($this->message, $this->maxLength), 'data-rule-maxlength' => $this->maxLength);
        if ($this->minLength > 0)
            $attrs['data-msg-minlength'] = sprintf($this->message, $this->maxLength, $this->minLength);
            $attrs['data-rule-minlength'] = $this->minLength;
        
        return $attrs;
    }

    public function validate(\Amvisie\Core\BaseModel &$model, $property) : bool
    {
        $length = strlen($model->{$property});
        return !($length < $this->minLength || $length > $this->maxLength);
    }
}
