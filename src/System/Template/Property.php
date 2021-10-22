<?php

namespace System\Template;

use System\Template\Traits\CommentTrait;
use System\Template\Traits\FormatterTrait;

class Property
{
  use FormatterTrait;
  use CommentTrait;

  private $is_static = false;
  private $visibility = self::PRIVATE_;
  public const PUBLIC_ = 0;
  public const PRIVATE_ = 1;
  public const PROTECTED_ = 2;

  private $name;
  private $expecting;

  public function __construct(string $name)
  {
    $this->name = $name;
  }

  public function __toString()
  {
    return $this->generate();
  }

  public static function new(string $name)
  {
    return new self($name);
  }

  private function planTemplate(): string
  {
    return $this->customize_template ?? "{{comment}}{{visibility}}{{static}}{{name}}{{expecting}};";
  }

  public function generate(): string
  {
    $tempalate = $this->planTemplate();
    $tab_dept = fn(int $dept) => str_repeat($this->tab_indent, ($dept * $this->tab_size));

    $comment = $this->generateComment(1);
    $comment = count($this->comments) > 0
      ? $comment . "\n" . $tab_dept(1)
      : $comment;

    // generate visibility
    $visibility = '';
    switch ($this->visibility) {
      case self::PUBLIC_:
        $visibility = "public ";
        break;

      case self::PROTECTED_:
        $visibility = "protected ";
        break;

      case self::PRIVATE_:
        $visibility = "private ";
        break;
    }

    // generate static
    $static = $this->is_static ? "static " : "";

    // generate name
    $name = '$' . $this->name;

    // generate value or expecting
    $expecting = $this->expecting == null
      ? ""
      : " " . $this->expecting;

    // final
    return str_replace(
      ["{{comment}}", "{{visibility}}", "{{static}}", "{{name}}", "{{expecting}}"],
      [$comment, $visibility, $static, $name, $expecting],
      $tempalate
    );
  }

  // setter
  public function setStatic(bool $is_static = true)
  {
    $this->is_static = $is_static;
    return $this;
  }

  public function visibility(int $visibility = self::PUBLIC_)
  {
    $this->visibility = $visibility;

    return $this;
  }
  public function name(string $name)
  {
    $this->name = $name;
    return $this;
  }

  public function expecting(string $expecting)
  {
    $this->expecting = $expecting;
    return $this;
  }
}
