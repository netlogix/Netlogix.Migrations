<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Tests\Unit\Domain\Service;

use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Domain\Model\MigrationStatus;
use Netlogix\Migrations\Domain\Repository\MigrationStatusRepository;
use Netlogix\Migrations\Domain\Service\VersionLogger;
use Netlogix\Migrations\Domain\Service\VersionResolver;

class VersionLoggerTest extends UnitTestCase
{
    /**
     * @var VersionLogger
     */
    private $versionLogger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MigrationStatusRepository
     */
    private $migrationStatusRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $versionResolver = $this->getMockBuilder(VersionResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $versionResolver->method('extractVersion')
            ->willReturn('1910');

        $this->migrationStatusRepository = $this->getMockBuilder(MigrationStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $persistenceManager = $this->getMockBuilder(PersistenceManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->versionLogger = new VersionLogger(
            $versionResolver,
            $this->migrationStatusRepository,
            $persistenceManager
        );
    }

    /**
     * @test
     */
    public function Can_Store_New_MigrationStatus_On_Up()
    {
        $migration = $this->createMock(Migration::class);

        $this->migrationStatusRepository->expects($this->once())
            ->method('add');

        $this->versionLogger->logMigration($migration, 'up');
    }

    /**
     * @test
     */
    public function Can_Remove_MigrationStatus_On_Down()
    {
        $migration = $this->createMock(Migration::class);

        $migrationStatusMock = $this->createMock(MigrationStatus::class);

        $this->migrationStatusRepository->method('findByIdentifier')
            ->with('1910')
            ->willReturn($migrationStatusMock);

        $this->migrationStatusRepository->expects($this->once())
            ->method('remove')
            ->with($migrationStatusMock);

        $this->versionLogger->logMigration($migration, 'down');
    }
}
