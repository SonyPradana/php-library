<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Decorate;
use System\Console\Style\Style;

trait AlertTrait
{
    /** @var int margin left */
    protected $margin_left = 0;

    /**
     * Set margin left.
     *
     * @param int $margin_left
     *
     * @return self
     */
    public function marginLeft($margin_left)
    {
        $this->margin_left = $margin_left;

        return $this;
    }

    /**
     * Render alert info.
     *
     * @param string $info
     *
     * @return Style
     */
    public function info($info)
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->margin_left)
            ->push(' info ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgBlue()
            ->push(' ')
            ->push($info)
            ->newLines(2)
        ;
    }

    /**
     * Render alert warning.
     *
     * @param string $warn
     *
     * @return Style
     */
    public function warn($warn)
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->margin_left)
            ->push(' warn ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgYellow()
            ->push(' ')
            ->push($warn)
            ->newLines(2)
        ;
    }

    /**
     * Render alert fail.
     *
     * @param string $fail
     *
     * @return Style
     */
    public function fail($fail)
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->margin_left)
            ->push(' fail ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgRed()
            ->push(' ')
            ->push($fail)
            ->newLines(2)
        ;
    }

    /**
     * Render alert ok (similar with success).
     *
     * @param string $ok
     *
     * @return Style
     */
    public function ok($ok)
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->margin_left)
            ->push(' ok ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgGreen()
            ->push(' ')
            ->push($ok)
            ->newLines(2)
        ;
    }
}
