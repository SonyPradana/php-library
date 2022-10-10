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
        return $this->alert(
            $this->margin()->push(' INFO ')->bold()->bgBlue(),
            new Style($info)
        )->new_lines(2);
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
        return $this->alert(
            $this->margin()->push(' WARN ')->bold()->bgYellow(),
            new Style($warn)
        )->new_lines(2);
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
        return $this->alert(
            $this->margin()->push(' FAIL ')->bold()->bgRed(),
            new Style($fail)
        )->new_lines(2);
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
        return $this->alert(
            $this->margin()->push(' OK ')->bold()->bgGreen(),
            new Style($ok)
        )->new_lines(2);
    }

    private function margin()
    {
        return (new Style())->new_lines()->repeat(' ', $this->margin_left);
    }

    public function alert(Style $header, Style $body)
    {
        return (new Style())
            ->tap($header)
            ->push(' ')
            ->tap($body)
        ;
    }
}
