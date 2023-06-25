<?php

declare(strict_types=1);

namespace System\Template;

use System\Template\Traits\CommentTrait;
use System\Template\Traits\FormatterTrait;

class Property
{
    use FormatterTrait;
    use CommentTrait;

    private bool $is_static      = false;
    private int $visibility      = self::PRIVATE_;
    public const PUBLIC_         = 0;
    public const PRIVATE_        = 1;
    public const PROTECTED_      = 2;

    private string $data_type;
    private string $name;
    /** @var string[] */
    private $expecting;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->generate();
    }

    public static function new(string $name): self
    {
        return new self($name);
    }

    private function planTemplate(): string
    {
        return $this->customize_template ?? '{{comment}}{{visibility}}{{static}}{{data type}}{{name}}{{expecting}};';
    }

    public function generate(): string
    {
        $tempalate = $this->planTemplate();
        $tab_dept  = fn (int $dept) => str_repeat($this->tab_indent, $dept * $this->tab_size);

        $comment = $this->generateComment(1);
        $comment = count($this->comments) > 0
      ? $comment . "\n" . $tab_dept(1)
      : $comment;

        // generate visibility
        $visibility = '';
        switch ($this->visibility) {
            case self::PUBLIC_:
                $visibility = 'public ';
                break;

            case self::PROTECTED_:
                $visibility = 'protected ';
                break;

            case self::PRIVATE_:
                $visibility = 'private ';
                break;
        }

        // generate static
        $static = $this->is_static ? 'static ' : '';

        // data type
        $data_type = $this->data_type ?? '';

        // generate name
        $name = '$' . $this->name;

        // generate value or expecting
        $expecting = '';
        if ($this->expecting !== null) {
            $single_line  = $this->expecting[0] ?? '';
            $multy_line   = implode(
                "\n" . $tab_dept(1),
                array_filter($this->expecting, fn ($key) => $key > 0, ARRAY_FILTER_USE_KEY)
            );
            $expecting = count($this->expecting) > 1
        ? ' ' . $single_line . "\n" . $tab_dept(1) . $multy_line
        : ' ' . $single_line;
        }

        // final
        return str_replace(
            ['{{comment}}', '{{visibility}}', '{{static}}', '{{data type}}', '{{name}}', '{{expecting}}'],
            [$comment, $visibility, $static, $data_type, $name, $expecting],
            $tempalate
        );
    }

    // setter
    public function setStatic(bool $is_static = true): self
    {
        $this->is_static = $is_static;

        return $this;
    }

    public function visibility(int $visibility = self::PUBLIC_): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function dataType(string $data_type): self
    {
        $this->data_type = $data_type . ' ';

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string|string[] $expecting Add expecting as string or array for multy line
     */
    public function expecting($expecting): self
    {
        $this->expecting = is_array($expecting)
            ? $expecting
            : [$expecting];

        return $this;
    }
}
