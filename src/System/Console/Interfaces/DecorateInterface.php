<?php

namespace System\Console\Interfaces;

interface DecorateInterface
{
    /**
     * Reset all attributes (set reset decorate to be 0).
     *
     * @return self
     */
    public function resetDecorate();

    /**
     * Text decorate bold.
     *
     * @return self
     */
    public function bold();

    /**
     * Text decorate underline.
     *
     * @return self
     */
    public function underline();

    /**
     * Text decorate blink.
     *
     * @return self
     */
    public function blink();

    /**
     * Text decorate reverse/invert.
     *
     * @return self
     */
    public function reverse();

    /**
     * Text decorate hidden.
     *
     * @return self
     */
    public function hidden();

    /**
     * Add raw terminal code.
     *
     * @param RuleInterface|string $raw Raw terminal code
     *
     * @return self
     */
    public function raw($raw);

    /**
     * @param int[] $reset rules reset
     *
     * @return self
     */
    public function rawReset($reset);

    /**
     * Set text color.
     *
     * @param ForegroundColor|string $color
     *
     * @return self
     */
    public function textColor($color);

    /**
     * Set Background color.
     *
     * @param BackgroundColor|string $color
     *
     * @return self
     */
    public function bgColor($color);
}
