<?php

namespace System\Template\Providers;

use System\Template\Method;

class NewFunction
{
  public static function name(string $name)
  {
    return new Method($name);
  }
}
