<?php

declare(strict_types=1);

namespace System\Template;

use System\Template\Traits\CommentTrait;
use System\Template\Traits\FormatterTrait;

class Generate
{
    use FormatterTrait;
    use CommentTrait;

    // for config
    private bool $is_final = false;
    private int $rule;
    private bool $end_with_newline = false;
    public const SET_CLASS         = 0;
    public const SET_ABSTRACT      = 1;
    public const SET_TRAIT         = 2;

    // builder property
    private ?string $name      = null;
    private ?string $namespace = null;
    /** @var string[] */
    private $uses           = [];
    private ?string $extend = null;
    /** @var string[] */
    private $implements = [];
    /** @var string[] */
    private $traits     = [];
    /** @var string[] */
    private $consts     = [];
    /** @var string[] */
    private $propertys  = [];
    /** @var string[] */
    private $methods    = [];
    /** @var string[] */
    private $body       = [];

    /** @var string[][] */
    private $pre_replace = [[], []];
    /** @var string[][] */
    private $replace     = [[], []];

    public function __construct(string $name = 'NewClass')
    {
        $this->name = $name;
        $this->rule = Generate::SET_CLASS;
    }

    public function __invoke(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public static function static(string $name): self
    {
        return new self($name);
    }

    public function __toString()
    {
        return $this->generate();
    }

    private function planTemplate(): string
    {
        return $this->customize_template ?? "<?php\n{{before}}{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}\n}{{end}}";
    }

    public function generate(): string
    {
        // pre replace
        $class = str_replace(
            $this->pre_replace[0],
            $this->pre_replace[1],
            $this->planTemplate()
        );

        $tab_dept = fn (int $dept) => str_repeat($this->tab_indent, $dept * $this->tab_size);

        // scope: before
        $before = [];
        if ($this->namespace !== null || count($this->uses) > 0) {
            $before[] = '';
        }

        // generte namespace
        if ($this->namespace !== null) {
            $before[] = 'namespace ' . $this->namespace . ";\n";
        }

        // genertae uses
        if (count($this->uses) > 0) {
            $before[] = 'use ' . implode(";\nuse ", $this->uses) . ';';
            $before[] = '';
        }

        $before = implode("\n", $before);

        // scope comment, generate commnet
        $comment = $this->generateComment(0, $this->tab_indent);

        // genarete class rule
        $rule = $this->rule == 0
            ? ''
            : $this->ruleText() . ' ';

        // generate final
        $rule = !$this->is_final
            ? $rule
            : 'final ' . $rule;

        // scope: head
        // genrete class name
        $head = [];

        // generete class name
        $head[] = $this->name;

        // generte extend
        if ($this->extend !== null) {
            $head[] = 'extends ' . $this->extend;
        }

        // generete implements
        if (count($this->implements) > 0) {
            $head[] = 'implements ' . implode(', ', $this->implements);
        }

        $head = implode(' ', $head);

        // scope: body
        $body = [];
        // generte traits
        if (count($this->traits) > 0) {
            $body[] = $tab_dept(1) . 'use ' . implode(', ', $this->traits) . ";\n";
        }

        // genrete consts
        $consts = [];
        if (count($this->consts) > 0) {
            foreach ($this->consts as $const) {
                /* @phpstan-ignore-next-line */
                if ($const instanceof Constant) {
                    $const
                        ->tabSize($this->tab_size)
                        ->tabIndent($this->tab_indent);
                    $consts[] = $tab_dept(1) . $const->generate();
                }
            }

            $consts[] = '';
        }
        $body[] = implode("\n", $consts);

        // genrete property
        $propertys = [];
        if (count($this->propertys) > 0) {
            foreach ($this->propertys as $property) {
                /* @phpstan-ignore-next-line */
                if ($property instanceof Property) {
                    $property
                        ->tabSize($this->tab_size)
                        ->tabIndent($this->tab_indent);
                    $propertys[] = $tab_dept(1) . $property->generate();
                }
            }

            $propertys[] = '';
        }
        $body[] = implode("\n", $propertys);

        // genete funtions
        $methods = [];
        if (count($this->methods) > 0) {
            foreach ($this->methods as $method) {
                /* @phpstan-ignore-next-line */
                if ($method instanceof Method) {
                    $method
                        ->tabSize($this->tab_size)
                        ->tabIndent($this->tab_indent);
                    $methods[] = $tab_dept(1) . $method->generate();
                }
            }

            $methods[] = '';
        }
        $body[] = implode("\n\n", array_filter($methods));

        // generate raw body
        if (count($this->body) > 0) {
            $body[] = $tab_dept(1) . implode("\n" . $tab_dept(2), $this->body);
        }

        $body = implode("\n", array_filter($body));

        // end with new line
        $end = $this->end_with_newline ? "\n" : '';

        // manual replace
        $search  = $this->replace[0] ?? null;
        $replace = $this->replace[1] ?? null;

        return str_replace(
            ['{{before}}', '{{comment}}', '{{rule}}', '{{head}}', '{{body}}', '{{end}}', ...$search],
            [$before, $comment, $rule, $head, $body, $end, ...$replace],
            $class
        );
    }

    private function ruleText(): string
    {
        return match ($this->rule) {
            self::SET_CLASS, self::SET_ABSTRACT => 'abstract',
            self::SET_TRAIT => 'trait',
            default         => '',
        };
    }

    /**
     * @return int|false
     */
    public function save(string $path_to_save)
    {
        return file_put_contents($path_to_save . '/' . $this->name . '.php', $this->generate());
    }

    // setter property

    public function rule(int $rule = self::SET_CLASS): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function setFinal(bool $isFinal = true): self
    {
        $this->is_final = $isFinal;

        return $this;
    }

    public function setEndWithNewLine(bool $enable = true): self
    {
        $this->end_with_newline = $enable;

        return $this;
    }

    // setter

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function use(string $use_namespace): self
    {
        $this->uses[] = $use_namespace;

        return $this;
    }

    /**
     * @param string[] $uses_namespace
     */
    public function uses(array $uses_namespace): self
    {
        $this->uses = $uses_namespace;

        return $this;
    }

    public function extend(string $extend): self
    {
        $this->extend = $extend;

        return $this;
    }

    public function implement(string $implement): self
    {
        $this->implements[] = $implement;

        return $this;
    }

    /**
     * @param string[] $implements
     */
    public function implements(array $implements): self
    {
        $this->implements = $implements;

        return $this;
    }

    public function trait(string $trait): self
    {
        $this->traits[] = $trait;

        return $this;
    }

    /**
     * @param string[] $traits
     */
    public function traits(array $traits): self
    {
        $this->traits = $traits;

        return $this;
    }

    public function body(string $raw_body): self
    {
        $this->body[] = $raw_body;

        return $this;
    }

    // setter - other
    public function addConst(string $name = 'NEW_CONST'): Constant
    {
        return $this->consts[] = new Constant($name);
    }

    /**
     * @param callable(ConstPool): void|Constant|ConstPool $new_const callabe with param pools constan, single constans or constPool
     */
    public function consts($new_const): self
    {
        // detect if single const
        if ($new_const instanceof Constant) {
            $this->consts[] = $new_const;
        }

        // detect if multy const with constPool
        elseif (is_callable($new_const)) {
            $const = new ConstPool();

            call_user_func_array($new_const, [$const]);

            foreach ($const->getPools() as $pool) {
                if ($pool instanceof Constant) {
                    $this->consts[] = $pool;
                }
            }
        }

        // detect parameter is instance constPool
        elseif ($new_const instanceof ConstPool) {
            foreach ($new_const->getPools() as $pool) {
                if ($pool instanceof Constant) {
                    $this->consts[] = $pool;
                }
            }
        }

        return $this;
    }

    public function addProperty(string $name = 'new_property'): Property
    {
        return $this->propertys[] = new Property($name);
    }

    /**
     * @param callable(PropertyPool): void|Property|PropertyPool $new_property callabe with param pools constan or single property
     */
    public function propertys($new_property): self
    {
        // detect if single propertys
        if ($new_property instanceof Property) {
            $this->propertys[] = $new_property;
        }

        // detect if multy property with porpertyPool
        elseif (is_callable($new_property)) {
            $property = new PropertyPool();

            call_user_func_array($new_property, [$property]);

            foreach ($property->getPools() as $pool) {
                if ($pool instanceof Property) {
                    $this->propertys[] = $pool;
                }
            }
        }

        // detect parameter is instance methodpool
        elseif ($new_property instanceof PropertyPool) {
            foreach ($new_property->getPools() as $pool) {
                if ($pool instanceof Property) {
                    $this->propertys[] = $pool;
                }
            }
        }

        return $this;
    }

    public function addMethod(string $name = 'new_method'): Method
    {
        return $this->methods[] = new Method($name);
    }

    /**
     * @param callable(MethodPool): void|Method|MethodPool $new_method callabe with param pools constan or single property
     */
    public function methods($new_method): self
    {
        // detect if single propertys
        if ($new_method instanceof Method) {
            $this->methods[] = $new_method;
        }

        // detect if multy property with methodspool
        elseif (is_callable($new_method)) {
            $method = new MethodPool();

            call_user_func_array($new_method, [$method]);

            foreach ($method->getPools() as $pool) {
                if ($pool instanceof Method) {
                    $this->methods[] = $pool;
                }
            }
        }
        // detect parameter is instance methodpool
        elseif ($new_method instanceof MethodPool) {
            foreach ($new_method->getPools() as $pool) {
                if ($pool instanceof Method) {
                    $this->methods[] = $pool;
                }
            }
        }

        return $this;
    }

    /**
     * @param string|string[] $search  Text to replace
     * @param string|string[] $replace Text replacer
     */
    public function preReplace($search, $replace): self
    {
        $search  = is_array($search) ? $search : [$search];
        $replace = is_array($replace) ? $replace : [$replace];

        $this->pre_replace = [$search, $replace];

        return $this;
    }

    /**
     * @param string|string[] $search  Text to replace
     * @param string|string[] $replace Text replacer
     */
    public function replace($search, $replace): self
    {
        $search  = is_array($search) ? $search : [$search];
        $replace = is_array($replace) ? $replace : [$replace];

        $this->replace = [$search, $replace];

        return $this;
    }
}
