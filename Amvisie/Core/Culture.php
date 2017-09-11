<?php
declare(strict_types=1);

namespace Amvisie\Core;

class Culture
{
    private $locale = '';
    private $systemLocale = '';

    public function __construct(string $locale)
    {
        if ($locale !== 'C') {
            $trimmed_lc = \trim($locale);

            $this->locale = \str_ireplace('utf8', '', $trimmed_lc);
            $this->systemLocale = $trimmed_lc;
        }
    }

    public function isEmpty() : bool
    {
        return \strlen($this->locale) === 0;
    }

    public function locale() : string
    {
        return $this->locale;
    }

    private static $culture;

    public static function setCulture(string $locale) : void
    {
        $usedLocale = \setlocale(LC_ALL, $locale, "$locale.utf8");
        if ($usedLocale === false) {
            throw new \Exception("'$locale' locale functionality is not implemented on your platform.");
        }

        self::$culture = new self($usedLocale);
    }

    public static function getCulture() : self
    {
        if (self::$culture == null) {
            self::$culture = new self('C');
        }

        return self::$culture;
    }
}
