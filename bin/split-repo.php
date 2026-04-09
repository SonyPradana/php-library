<?php

declare(strict_types=1);

use System\Console\Command;
use System\Console\Style\ProgressBar;
use System\Template\VarExport;

use function System\Console\fail;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

require_once __DIR__ . '../../vendor/autoload.php';

$command = new class($argv) extends Command {
    public function entry(): int
    {
        return match (true) {
            'validate' === $this->CMD => $this->validate(),
            'release' === $this->CMD  => $this->release(),
            default                   => 0,
        };
    }

    public function release(): int
    {
        // load split-repo config
        $config                = $this->loadConfig();
        $config['tag_version'] = $this->getNextVersion($config['tag_version']);

        // update spli config
        $this->writeSplitConfig($config);

        // reales new composer version
        $this->relealesNextVersion(
            $config['tag_version'],
            $config['split_repositorys']
        );

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

            if (
                false === $this->validateComposerVersion(
                    $composer_path = dirname(__DIR__) . $path . 'composer.json',
                    $packages,
                    $version,
                )
            ) {
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
     * @param array<string, string> $split_repositorys
     */
    public function relealesNextVersion(string $tag_version, array $split_repositorys): void
    {
        $paths                 = array_values($split_repositorys);
        $packages              = array_keys($split_repositorys);
        $progressbar           = new ProgressBar();
        $progressbar->complete = static fn (): string => (string) ok('Done, success update composer version.');
        $progressbar->maks     = count($paths);

        foreach ($paths as $path) {
            $composer = $this->updateComposerVersion(dirname(__DIR__) . $path . 'composer.json', $packages, $tag_version);

            $progressbar->current++;
            $progressbar->tickWith(':progress :percent :name', [
                ':name' => fn ($current, $maks) => $composer['name'],
            ]);
        }
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

    private function getNextVersion(string $current_version): string
    {
        if ($this->hasOption('tag')) {
            return $this->option('tag') ?? '';
        }

        $major = $this->option('next-major', false);
        $minor = $this->option('next-minor', false);
        $path  = $this->option('next-patch', false);

        return $this->createVersion($current_version, $major, $minor, $path);
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
     * @param array<string, string|array<string, string>> $config
     */
    private function writeSplitConfig(array $config): bool
    {
        $export = new VarExport();
        $export->setAlignArray();

        return $export->compile($config, __DIR__ . '../../split-repo.php');
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

    private function createVersion(string $tag_version, bool $major, bool $minor, bool $patch): string
    {
        [
            'major'      => $v_major,
            'minor'      => $v_minor,
            'patch'      => $v_patch,
            'prerelease' => $v_prerelease,
            'prefix'     => $prefix,
        ] = $this->parseTagVersion($tag_version);

        $version_new = match (true) {
            $major  => sprintf('%d.0.0', $v_major + 1),
            $minor  => sprintf('%d.%d.0', $v_major, $v_minor + 1),
            $patch  => sprintf('%d.%d.%d', $v_major, $v_minor, $v_patch + 1),
            default => sprintf('%d.%d.%d', $v_major, $v_minor, $v_patch),
        };

        $prerelease = $v_prerelease ? "-{$v_prerelease}" : '';

        return "{$prefix}{$version_new}{}{$prerelease}";
    }

    /**
     * @return array{major: int, minor: int, patch: int, prerelease: string, prefix: string}
     */
    private function parseTagVersion(string $tag_version): array
    {
        [
            'version' => $version,
            'prefix'  => $prefix,
        ]       = extract_version_prefix($tag_version);
        $parsed = parse_semver($version);

        return [
            'major'      => $parsed['major'],
            'minor'      => $parsed['minor'],
            'patch'      => $parsed['patch'],
            'prerelease' => $parsed['prerelease'],
            'prefix'     => $prefix,
        ];
    }
};

// helper

class InvalidSemVerException extends RuntimeException
{
}

/**
 * @return array{major: int, minor: int, patch: int, prerelease: string|null}
 *
 * @throws InvalidSemVerException
 */
function parse_semver(string $version): array
{
    if ('' === $version) {
        throw new InvalidSemVerException('Version must be a non-empty string');
    }

    $versionWithoutBuild        = strip_build_metadata($version);
    [$coreVersion, $prerelease] = split_core_and_prerelease($versionWithoutBuild);
    $coreNumbers                = split_core_version($coreVersion);

    $major = parse_version_number($coreNumbers[0], 'major');
    $minor = parse_version_number($coreNumbers[1], 'minor');
    $patch = parse_version_number($coreNumbers[2], 'patch');

    if (null !== $prerelease) {
        validate_prerelease($prerelease);
    }

    return [
        'major'      => $major,
        'minor'      => $minor,
        'patch'      => $patch,
        'prerelease' => $prerelease,
    ];
}

/**
 * Extract version prefix (^, ~, >, >=, <, <=, *, =, dll) dari version string.
 *
 * @return array{version: string, prefix: string}
 */
function extract_version_prefix(string $version_string): array
{
    // Supported prefix: ^, ~, >, >=, <, <=, =, *, v (optional)
    if (preg_match('/^([v~*=><^]+)(.+)$/', $version_string, $matches)) {
        return [
            'prefix'  => $matches[1],
            'version' => $matches[2],
        ];
    }

    // Default prefix
    return [
        'prefix'  => '^',
        'version' => ltrim($version_string, 'v'),
    ];
}

/** @throws InvalidSemVerException */
function strip_build_metadata(string $version): string
{
    $parts = explode('+', $version, 2);

    if (isset($parts[1]) && '' === $parts[1]) {
        throw new InvalidSemVerException('Build metadata cannot be empty if + is present');
    }

    return $parts[0];
}

/**
 * @return array{0: string, 1: string|null}
 */
function split_core_and_prerelease(string $version): array
{
    $parts = explode('-', $version, 2);

    return [$parts[0], $parts[1] ?? null];
}

/**
 * @return string[]
 *
 * @throws InvalidSemVerException
 */
function split_core_version(string $coreVersion): array
{
    $parts = explode('.', $coreVersion);
    $count = count($parts);

    if (3 !== $count) {
        throw new InvalidSemVerException(sprintf('Core version must have exactly 3 parts (major.minor.patch), got: %d', $count));
    }

    return $parts;
}

/** @throws InvalidSemVerException */
function parse_version_number(string $part, string $partName): int
{
    if (!ctype_digit($part)) {
        throw new InvalidSemVerException(sprintf('%s must be numeric, got: \'%s\'', $partName, $part));
    }

    if (strlen($part) > 1 && '0' === $part[0]) {
        throw new InvalidSemVerException(sprintf('%s cannot have leading zeros, got: \'%s\'', $partName, $part));
    }

    if ((string) (int) $part !== $part) {
        throw new InvalidSemVerException(sprintf('%s value is too large: \'%s\'', $partName, $part));
    }

    return (int) $part;
}

/** @throws InvalidSemVerException */
function validate_prerelease(string $prerelease): void
{
    if ('' === $prerelease) {
        throw new InvalidSemVerException('Prerelease cannot be empty if hyphen is present');
    }

    foreach (explode('.', $prerelease) as $identifier) {
        validate_prerelease_identifier($identifier);
    }
}

/** @throws InvalidSemVerException */
function validate_prerelease_identifier(string $identifier): void
{
    if ('' === $identifier) {
        throw new InvalidSemVerException('Prerelease cannot have empty identifiers');
    }

    if (ctype_digit($identifier)) {
        if (strlen($identifier) > 1 && '0' === $identifier[0]) {
            throw new InvalidSemVerException(sprintf('Numeric prerelease identifier cannot have leading zeros, got: \'%s\'', $identifier));
        }

        return;
    }

    if (!preg_match('/^[0-9a-zA-Z-]+$/', $identifier)) {
        throw new InvalidSemVerException(sprintf('Alphanumeric prerelease identifier can only contain alphanumeric and hyphens, got: \'%s\'', $identifier));
    }
}

// core
exit($command->entry());
