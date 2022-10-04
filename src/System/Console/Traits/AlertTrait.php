<?php

namespace System\Console\Traits;

use System\Console\Style\Style;

trait AlertTrait
{
    /** @var int margin left */
    protected $margin_left = 0;

    public function marginLeft($margin_left)
    {
        $this->margin_left = $margin_left;

        return $this;
    }

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
            ->new_lines(2);
    }

    public function warn($info)
    {
        return (new Style())
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' warn ')
            ->bold()
            ->bgYellow()
            ->push(' ')
            ->push($info)
            ->new_lines(2);
    }

    public function fail($info)
    {
        return (new Style())
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' fail ')
            ->bold()
            ->bgRed()
            ->push(' ')
            ->push($info)
            ->new_lines(2);
    }

    public function success($info)
    {
        return (new Style())
            ->new_lines()
            ->repeat(' ', $this->margin_left)
            ->push(' success ')
            ->bold()
            ->bgGreen()
            ->push(' ')
            ->push($info)
            ->new_lines(2);
    }
}
