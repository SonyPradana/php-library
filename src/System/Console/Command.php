<?php

namespace System\Console;

class Command
{
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

  protected function rules(array $rule, string $text, bool $reset = true): string
  {
    $toString = implode(";", $rule);
    $string   = "\e[$toString" . "m$text";
    return $reset ? $string . "\e[0m" : $string;
  }

  protected function prints(array $string): void
  {
    foreach ($string as $print) {
      echo $print;
    }
  }

  protected function print_n(int $count = 1): void
  {
    for ($i = 0; $i < $count; $i++) {
      echo "\n";
    }
  }

  protected function print_t(int $count = 1): void
  {
    for ($i = 0; $i < $count; $i++) {
      echo "\t";
    }
  }

  protected function newLine(int $count = 1): string
  {
    $res = '';
    for ($i = 0; $i < $count; $i++) {
      $res .= "\n";
    }
    return $res;
  }

  protected function tabs(int $count = 1): string
  {
    $res = '';
    for ($i = 0; $i < $count; $i++) {
      $res .= "\t";
    }
    return $res;
  }

  /** code (bash): 31 */
  protected function textRed(string $text): string
  {
    return "\e[31m$text\e[0m";
  }

  /** code (bash): 33 */
  protected function textYellow(string $text): string
  {
    return "\e[33m$text\e[0m";
  }

  /** code (bash): 32 */
  protected function textGreen(string $text): string
  {
    return "\e[32m$text\e[0m";
  }

  /** code (bash): 34 */
  protected function textBlue(string $text): string
  {
    return "\e[34m$text\e[0m";
  }

  /** code (bash): 2 */
  protected function textDim(string $text): string
  {
    return "\e[2m$text\e[0m";
  }

  /** code (bash): 97 */
  protected function textWhite(string $text): string
  {
    return "\e[97m$text\e[0m";
  }

  /** code (bash): 41 */
  protected function bgRed(string $text): string
  {
    return "\e[41m$text\e[0m";
  }

  /** code (bash): 43 */
  protected function bgYellow(string $text): string
  {
    return "\e[43m$text\e[0m";
  }

  /** code (bash): 42 */
  protected function bgGreen(string $text): string
  {
    return "\e[42m$text\e[0m";
  }

  /** code (bash): 44 */
  protected function bgBlue(string $text): string
  {
    return "\e[44m$text\e[0m";
  }

  /** code (bash): 107 */
  protected function bgWhite(string $text): string
  {
    return "\e[107m$text\e[0m";
  }
}
