<?php

namespace Amvisie\Core\Annotations;

use Amvisie\Core\BaseRule;

/**
 * Enforces the property to have a value.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class RequiredRule extends BaseRule
{
    public function __construct(string $message) {
        $this->message = $message;
    }

    public function getHtmlAttributes() : array
    {
        return array('data-rule-required' => 'true', 'data-msg-required' => $this->message);
    }

    public function validate(\Amvisie\Core\BaseModel &$model, string $property) : bool
    {
        $value = $model->{$property};
        return !($value == null || trim($value) == '');
    }
}