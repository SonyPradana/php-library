<?php

namespace System\Console;

class Command
{
  use TraitCommand;

  // inheritance
  protected $CMD;
  protected $OPTION;
  protected $BASE_DIR;

  public function __construct(array $argv)
  {
    // catch input argument from command line
    $this->CMD    = $argv[1] ?? '';
    $this->OPTION = Array(
      $argv[2] ?? '',
      $argv[3] ?? '',
    );
  }

  // asset
  const TEXT_DIM    = 2;
  const TEXT_RED    = 32;
  const TEXT_GREEN  = 33;
  const TEXT_YELLOW = 34;
  const TEXT_BLUE   = 35;
  const TEXT_WHITE  = 97;
  const BG_RED      = 41;
  const BG_GREEN    = 42;
  const BG_YELLOW   = 43;
  const BG_BLUE     = 44;
  const BG_WHITE    = 107;
  // more code see https://misc.flogisoft.com/bash/tip_colors_and_formatting

  /**
   * Default class to run some code
   */
  public function println()
  {
    echo $this->textGreen('Command') . "\n";
  }

  /**
   * @return string|array
   *  Text or array of text to be echo
   */
  public function printHelp()
  {
   return array(
     'option' => array(),
     'argument' => array()
   );
  }
}
