<?php

namespace System\Template\Providers;

use System\Template\Property;

class Newproperty
{
  public static function name(string $name)
  {
    return new Property($name);
  }
}
