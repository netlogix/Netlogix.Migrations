<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Netlogix\Migrations\Domain\Repository\MigrationStatusRepository;

/**
 * @Flow\Scope("singleton")
 */
final class MigrationService
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MigrationStatusRepository
     */
    private $migrationStatusRepository;

    /**
     * @var FileSystemMigrationsResolver
     */
    private $fileSystemMigrationsResolver;

    /**
     * @var MigrationExecutor
     */
    private $migrationExecutor;

    /**
     * @var VersionResolver
     */
    private $versionResolver;

    public function __construct(
        ObjectManagerInterface $objectManager,
        MigrationStatusRepository $migrationStatusRepository,
        FileSystemMigrationsResolver $fileSystemMigrationsResolver,
        MigrationExecutor $migrationExecutor,
        VersionResolver $versionResolver
    ) {
        $this->objectManager = $objectManager;
        $this->migrationStatusRepository = $migrationStatusRepository;
        $this->fileSystemMigrationsResolver = $fileSystemMigrationsResolver;
        $this->migrationExecutor = $migrationExecutor;
        $this->versionResolver = $versionResolver;
    }


    public function findUnexecutedMigrations(): array
    {
        $availableMigrationClassNames = $this->findAvailableMigrationClassNames();
        foreach ($this->migrationStatusRepository->findAll() as $migrationStatus) {
            assert($migrationStatus instanceof MigrationStatus);
            unset($availableMigrationClassNames[$migrationStatus->getVersion()]);
        }

        return $this->getMigrations($availableMigrationClassNames);
    }



    private function getMigrations(?array $classNames = null): array
    {
        $classes = $classNames ?? $this->findAvailableMigrationClassNames();

        return array_map(function (string $className) {
            $reflectionClass = new \ReflectionClass($className);
            $constructor = $reflectionClass->getConstructor();
            $params = array_map(function(\ReflectionParameter $parameter) {
                $type = $parameter->getClass()->getName();

                return $this->objectManager->get($type);
            }, $constructor ? $constructor->getParameters() : []);

            return $this->objectManager->get($className, ...$params);
        }, $classes);
    }


    private function findAvailableMigrationClassNames(): array
    {
        $buildClassNames = function (): \Generator {
            $classNames = $this->fileSystemMigrationsResolver->findMigrationFiles();
            usort($classNames, function (string $classA, string $classB) {
                $simpleName = function (string $className) {
                    return (substr($className, strrpos($className, '\\') + 1));
                };

                return $simpleName($classA) <=> $simpleName($classB);
            });

            foreach ($classNames as $className) {
                yield $this->versionResolver->extractVersion($className) => $className;
            }
        };

        return iterator_to_array($buildClassNames());
    }

    public function executeMigrations(string $version, string $output, bool $quiet)
    {
        foreach ($this->findUnexecutedMigrations() as $migration) {
            $this->migrationExecutor->execute($migration);
        }
    }
}
