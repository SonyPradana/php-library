<?php

namespace System\Console\Traits;

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
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' info ')
            ->bold()
            ->bgBlue()
            ->push(' ')
            ->push($info)
            ->new_lines(2)
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
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' warn ')
            ->bold()
            ->bgYellow()
            ->push(' ')
            ->push($warn)
            ->new_lines(2)
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
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' fail ')
            ->bold()
            ->bgRed()
            ->push(' ')
            ->push($fail)
            ->new_lines(2)
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
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' ok ')
            ->bold()
            ->bgGreen()
            ->push(' ')
            ->push($ok)
            ->new_lines(2)
        ;
    }
}
