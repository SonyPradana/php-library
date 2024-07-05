<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Integrate\Application;
use System\Integrate\ConfigRepository;
use System\Integrate\ValueObjects\CommandMap;

final class Util
{
    /**
     * Convert command from array to CommandMap.
     *
     * @return CommandMap[]
     */
    public static function loadCommandFromConfig(Application $app): array
    {
        $command_map = [];
        foreach ($app[ConfigRepository::class]->get('commands', []) as $commands) {
            foreach ($commands as $command) {
                $command_map[] = new CommandMap($command);
            }
        }

        return $command_map;
    }
}
