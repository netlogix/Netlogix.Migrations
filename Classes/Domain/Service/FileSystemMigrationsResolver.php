<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Doctrine\DBAL\Migrations\Finder\GlobFinder;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageInterface;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;

/**
 * @Flow\Scope("singleton")
 */
class FileSystemMigrationsResolver
{
    /**
     * @var PackageManager
     */
    private $packageManager;

    /**
     * @var GlobFinder
     */
    private $globFinder;

    public function __construct(PackageManager $packageManager)
    {
        $this->packageManager = $packageManager;
        $this->globFinder = new GlobFinder();
    }

    /**
     * @return string[]
     */
    public function findMigrationFiles(): array
    {
        $classNames = [];
        /** @var PackageInterface $package */
        foreach ($this->packageManager->getAvailablePackages() as $package) {
            $path = Files::concatenatePaths([
                $package->getPackagePath(),
                'Migrations',
                'Netlogix'
            ]);

            if (is_dir($path)) {
                $migrations = $this->globFinder->findMigrations(
                    $path,
                    'Netlogix\Migrations\Persistence\Migrations'
                );

                if ($migrations !== []) {
                    array_push($classNames, ...$migrations);
                }
            }
        }
        return $classNames;
    }
}
