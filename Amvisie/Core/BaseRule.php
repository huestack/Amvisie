<?php
declare(strict_types=1);

namespace Amvisie\Core;
/**
 * A rule enforces data to be validated and verified against condition defined by derived classes.
 * @author Ritesh Gite <huestack@yahoo.com>
 */
abstract class BaseRule
{
    /**
     * An error message
     * @var string
     */
    protected $message;

    function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Gets an associative array of html attributes to include in tags for unobtrusive validation.
     * @return array An array of data-* attributes and their values.
     */
    abstract function getHtmlAttributes() : array;

    /**
     * Validates passed value against the rule.
     * @param BaseModel $model A reference to model object against which the value has to be validated.
     * @param string $property Name of property that has to be validated.
     * @return bool true if validation is successful; otherwise false.
     */
    abstract function validate(BaseModel &$model, string $property) : bool;

    /**
     * Gets an error message associated with validation rule.
     * @return string A message.
     */
    public function getMessage() : string
    {
        return $this->message ?? '';
    }
}
