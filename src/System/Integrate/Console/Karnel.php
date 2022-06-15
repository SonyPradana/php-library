<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Container\Container;

class Karnel
{
    /** @var Container */
    protected $app;
    /** @var int concole exit status */
    protected $exit_code;

    /**
     * Set instance.
     *
     * @param Container $app Application container
     * */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Handle input karnel.
     *
     * @param string|array<int, string> $input
     *
     * @return int Exit code
     */
    public function handle($input)
    {
        // hanlde

        return $this->exit_code = 0;
    }

    /**
     * Get karne exit status code.
     *
     * @return int Exit status code
     */
    public function exit_code()
    {
        return $this->exit_code;
    }
}
