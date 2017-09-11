<?php

namespace Amvisie\Core\Annotations;

use Amvisie\Core\BaseRule;

/**
 * Enforces the property to have value in specified pattern.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
class RegExRule extends BaseRule
{
    private $pattern;
    private $patternJS;


    public function __construct(string $message, string $pattern)
    {
        parent::__construct($message);
        //$pattern = preg_quote($pattern);
        $this->pattern = '/' . $pattern . '/';
        $this->patternJS = $pattern;
    }
    
    public function getHtmlAttributes() : array
    {
        return array('data-msg-regex' => $this->message,
            'data-rule-regex' => $this->patternJS);
    }

    public function validate(\Amvisie\Core\BaseModel &$model, string $property) : bool
    {
        $value = $model->{$property};
        return $value == '' || !(preg_match($this->pattern, $value) == 0);
    }
}
