<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;
use System\View\Exceptions\DirectiveCanNotBeRegister;
use System\View\Exceptions\DirectiveNotRegister;

class DirectiveTemplator extends AbstractTemplatorParse
{
    /**
     * @var array<string, \Closure>
     */
    private static array $directive = [];

    /**
     * Excludes list of directive alredy use by Templator.
     *
     * @var array<string, string>
     */
    public static array $excludeList = [
        'break'    => BreakTemplator::class,
        'continue' => ContinueTemplator::class,
        'else'     => IfTemplator::class,
        'extend'   => SectionTemplator::class,
        'foreach'  => EachTemplator::class,
        'if'       => IfTemplator::class,
        'include'  => IncludeTemplator::class,
        'json'     => JsonTemplator::class,
        'php'      => PHPTemplator::class,
        'raw'      => NameTemplator::class,
        'section'  => SectionTemplator::class,
        'set'      => SetTemplator::class,
        'use'      => UseTemplator::class,
    ];

    public static function register(string $name, \Closure $callable): void
    {
        if (array_key_exists($name, self::$excludeList)) {
            throw new DirectiveCanNotBeRegister($name, self::$excludeList[$name]);
        }

        self::$directive[$name] = $callable;
    }

    public static function call(string $name, mixed ...$parameters): string
    {
        if (false === array_key_exists($name, self::$directive)) {
            throw new DirectiveNotRegister($name);
        }

        $callback = self::$directive[$name];

        return (string) $callback(...$parameters);
    }

    public function parse(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*(\w+)\((.*?)\)\s*%}/',
            function ($matches) {
                $name   = $matches[1];
                $params = explode(',', $matches[2]);
                $params = array_map(fn ($param) => ltrim($param), $params);

                return array_key_exists($name, self::$excludeList)
                    ? $matches[0]
                    : '<?php echo System\View\Templator\DirectiveTemplator::call(\'' . $name . '\', ' . implode(', ', $params) . '); ?>'
                ;
            },
            $template
        );
    }
}
