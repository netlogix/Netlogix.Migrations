<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Neos\Flow\Persistence\PersistenceManagerInterface;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Domain\Model\MigrationStatus;
use Netlogix\Migrations\Domain\Repository\MigrationStatusRepository;

class VersionLogger
{
    /**
     * @var VersionResolver
     */
    private $versionResolver;

    /**
     * @var MigrationStatusRepository
     */
    private $migrationStatusRepository;

    /**
     * @var PersistenceManagerInterface
     */
    private $persistenceManager;

    public function __construct(
        VersionResolver $versionResolver,
        MigrationStatusRepository $migrationStatusRepository,
        PersistenceManagerInterface $persistenceManager
    ) {
        $this->versionResolver = $versionResolver;
        $this->migrationStatusRepository = $migrationStatusRepository;
        $this->persistenceManager = $persistenceManager;
    }

    public function logMigration(Migration $migration, string $direction): void
    {
        $version = $this->versionResolver->extractVersion(get_class($migration));
        switch ($direction) {
            case 'up':
                $migrationStatus = new MigrationStatus($version);
                $this->migrationStatusRepository->add($migrationStatus);
                break;
            case 'down':
                $migrationStatus = $this->migrationStatusRepository->findByIdentifier($version);
                $this->migrationStatusRepository->remove($migrationStatus);
                break;
        }
        $this->persistenceManager->persistAll();
    }
}
