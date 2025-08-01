<?php

declare(strict_types=1);

use System\Console\Command;
use System\Console\Style\ProgressBar;

use function System\Console\fail;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

require_once __DIR__ . '../../vendor/autoload.php';

$command = new class($argv) extends Command {
    public function entry(): int
    {
        return match (true) {
            'validate' === $this->CMD   => $this->validate(),
            'release' === $this->CMD    => $this->release(),
            default                     => 0,
        };
    }

    public function release(): int
    {
        $config                = $this->loadConfig();
        $version               = $config['tag_version'];
        $split_repositorys     = $config['split_repositorys'];
        $paths                 = array_values($split_repositorys);
        $packages              = array_keys($split_repositorys);
        $progressbar           = new ProgressBar();
        $progressbar->complete = static fn (): string => (string) ok('Done, success update composer version.');
        $progressbar->maks     = count($paths);

        foreach ($paths as $path) {
            $composer = $this->updateComposerVersion(dirname(__DIR__) . $path . 'composer.json', $packages, $version);

            $progressbar->current++;
            $progressbar->tickWith(':progress :percent :name', [
                ':name' => fn ($current, $maks) => $composer['name'],
            ]);
        }

        return 0;
    }

    public function validate(): int
    {
        $config                = $this->loadConfig();
        $version               = $config['tag_version'];
        $split_repositorys     = $config['split_repositorys'];
        $paths                 = array_values($split_repositorys);
        $packages              = array_keys($split_repositorys);
        $progressbar           = new ProgressBar();
        $progressbar->complete = static fn (): string => (string) ok('Done, your monorepo is greate!!!');
        $progressbar->maks     = count($paths);

        foreach ($paths as $path) {
            $validate_assets = $this->validateAssetsFile(dirname(__DIR__), $path);
            if (false === empty($validate_assets)) {
                foreach ($validate_assets as $error) {
                    $progressbar->complete = static fn (): string => (string) fail($error);
                    $progressbar->current  = $progressbar->maks + 1;
                    $progressbar->tick();

                    return 1;
                }
            }

            if (false === $this->validateComposerVersion($composer_path = dirname(__DIR__) . $path . 'composer.json', $packages, $version)) {
                $progressbar->complete = static fn (): string => (string) fail('failed!');
                $progressbar->current  = $progressbar->maks + 1;
                $progressbar->tick();

                warn('Conflig found')->out(false);

                if (file_exists($composer_path)) {
                    style('your version: ')->push($version)->textDim()->out();
                    $composer = $this->loadComposer($composer_path);
                    foreach ($composer['require'] as $package => $package_version) {
                        if (in_array($package, $packages)) {
                            style($package)->textYellow()->push(':')->push($package_version)->textDim()->out();
                        }
                    }
                }

                return 1;
            }

            $progressbar->current++;
            $progressbar->tick();
        }

        return 0;
    }

    /**
     * Update Composer pacakge version by compire with current verstion.
     *
     * @param string[] $packages
     *
     * @return array<string, mixed>
     */
    public function updateComposerVersion(string $path, array $packages, string $version): array
    {
        if (false === file_exists($path)) {
            throw new Exception('composer file not founded.');
        }

        $composer = $this->loadComposer($path);

        if (false === array_key_exists('require', $composer)) {
            return $composer;
        }

        $required = [];
        foreach ($composer['require'] as $package => $package_version) {
            if (in_array($package, $packages)) {
                $required[$package] = $version;
                unset($packages[$package]);
                continue;
            }

            $required[$package] = $package_version;
        }
        $composer['require'] = $required;
        $this->writeComposer($path, $composer);

        return $composer;
    }

    /**
     * Validate Composer pacakge version by compire with current verstion.
     *
     * @param string[] $packages
     */
    private function validateComposerVersion(string $path, array $packages, string $version): bool
    {
        if (false === file_exists($path)) {
            return true;
        }

        $composer = $this->loadComposer($path);

        if (false === array_key_exists('require', $composer)) {
            return true;
        }

        foreach ($composer['require'] as $package => $package_version) {
            if (in_array($package, $packages)) {
                if ($package_version !== $version) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate static asset file must include in split repo.
     *
     * @return string[]
     */
    private function validateAssetsFile(string $base, string $path): array
    {
        $errors = [];
        if (false === file_exists("{$base}{$path}LICENSE")) {
            $errors[] = "license not found in {$path}.";
        }

        if (false === file_exists("{$base}{$path}.github/workflows/close-pull-request.yml")) {
            $errors[] = "close pr github workflow not found in {$path}.";
        }

        return $errors;
    }

    /**
     * Load split configuration.
     *
     * @return array<string, string|array<string, string>>
     */
    private function loadConfig(): array
    {
        return require __DIR__ . '../../split-repo.php';
    }

    /**
     * Load composer as array.
     *
     * @return array<string, mixed>
     */
    private function loadComposer(string $path): array
    {
        $composer_file = file_get_contents($path);

        return json_decode($composer_file, true);
    }

    /**
     * Wrtire composer.json by given array of update composer.
     *
     * @param array<string, mixed> $composer
     */
    private function writeComposer(string $path, array $composer): bool
    {
        $json = \json_encode($composer, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);

        return false === file_put_contents($path, "{$json}\n") ? false : true;
    }
};

exit($command->entry());
