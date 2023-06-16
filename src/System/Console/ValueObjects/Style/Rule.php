<?php

declare(strict_types=1);

namespace System\Console\ValueObjects\Style;

use System\Console\Style\Decorate;

final class Rule
{
    /**
     * Array of command rule.
     *
     * @var array<int, array<int, string|int>>
     */
    private $raw_rules = [];

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    private $reset_rules = [Decorate::RESET];

    /**
     * Rule of text color.
     *
     * @var array<int, int>
     */
    private $text_color_rule = [Decorate::TEXT_DEFAULT];

    /**
     * Rule of background color.
     *
     * @var array<int, int>
     */
    private $bg_color_rule = [Decorate::BG_DEFAULT];

    /**
     * Rule of text decorate.
     *
     * @var array<int, int>
     */
    private $decorate_rules = [];

    /**
     * @param int[]                              $text_color_rule
     * @param int[]                              $bg_color_rule
     * @param int[]                              $decorate_rules
     * @param int[]                              $reset_rules
     * @param array<int, array<int, string|int>> $raw_rules
     */
    public function __construct(
        array $text_color_rule,
        array $bg_color_rule,
        array $decorate_rules,
        array $reset_rules,
        array $raw_rules,
    ) {
        $this->text_color_rule = $text_color_rule;
        $this->bg_color_rule   = $bg_color_rule;
        $this->decorate_rules  = $decorate_rules;
        $this->reset_rules     = $reset_rules;
        $this->raw_rules       = $raw_rules;
    }

    /**
     * @return int[]
     */
    public function TextColorRule(): array
    {
        return $this->text_color_rule;
    }

    /**
     * @return int[]
     */
    public function BackgroundColorRule(): array
    {
        return $this->bg_color_rule;
    }

    /**
     * @return int[]
     */
    public function DecorateRules(): array
    {
        return $this->decorate_rules;
    }

    /**
     * @return int[]
     */
    public function ResetRules(): array
    {
        return $this->reset_rules;
    }

    /**
     * @return array<int, int[]|string[]>
     */
    public function RawRules(): array
    {
        return $this->raw_rules;
    }
}
