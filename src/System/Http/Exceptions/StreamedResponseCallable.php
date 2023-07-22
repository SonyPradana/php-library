<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

/**
 * @internal
 */
final class StreamedResponseCallable extends \Exception
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('Stream callback must not be null');
    }
}
