<?php

namespace System\Template;

class MethodPool
{
  private $pools = [];

  public function name(string $name)
  {
    return $this->pools[] = new Method($name);
  }

  public function getPools(): array
  {
    return $this->pools;
  }

}
