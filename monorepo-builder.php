<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;

return static function (MBConfig $config): void {
    $config->packageDirectories([__DIR__ . '/src/System']);
    $config->disableDefaultWorkers();

    $config->workers([
        // change log
        AddTagToChangelogReleaseWorker::class,
        // update root composer json
        UpdateReplaceReleaseWorker::class,
        // update child composer json
        SetCurrentMutualDependenciesReleaseWorker::class,
    ]);
};
