<?php

namespace System\Template\Traits;

/**
 * Trait for format the printter
 */
trait FormatterTrait
{
  protected $tab_size = 1;
  protected $tab_indent = "\t";
  private $customize_template;

  public function tabSize(int $tab_size)
  {
    $this->tab_size = $tab_size;
    return $this;
  }

  public function tabIndent(string $tab_indent)
  {
    $this->tab_indent = $tab_indent;
    return $this;
  }

  public function customizeTemplate(string $template)
  {
    $this->customize_template = $template;
    return $this;
  }
}
