<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Tests\Unit\Domain\Service;

use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Domain\Model\MigrationStatus;
use Netlogix\Migrations\Domain\Repository\MigrationStatusRepository;
use Netlogix\Migrations\Domain\Service\FileSystemMigrationsResolver;
use Netlogix\Migrations\Domain\Service\MigrationService;
use Netlogix\Migrations\Domain\Service\VersionLogger;
use Netlogix\Migrations\Domain\Service\VersionResolver;
use Netlogix\Migrations\Error\UnknownMigration;

class MigrationServiceTest extends UnitTestCase
{
    /**
     * @var MigrationService
     */
    private $migrationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|VersionLogger
     */
    private $versionLogger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileSystemMigrationsResolver
     */
    private $fileSystemMigrationsResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    private $objectManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MigrationStatusRepository
     */
    private $migrationStatusRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->migrationStatusRepository = $this->getMockBuilder(MigrationStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->versionLogger = $this->getMockBuilder(VersionLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileSystemMigrationsResolver = $this->getMockBuilder(FileSystemMigrationsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileSystemMigrationsResolver->method('findMigrationFiles')
            ->willReturn([
                Version1312192::class,
                Version1312193::class,
            ]);

        $this->migrationService = new MigrationService(
            $this->objectManager,
            $this->migrationStatusRepository,
            $this->fileSystemMigrationsResolver,
            new VersionResolver()
        );
    }

    /**
     * @test
     */
    public function Can_Return_UnexecutedMigrations()
    {
        $migrationMock = $this->getMockBuilder(Migration::class)
            ->getMock();

        $this->objectManager->method('get')
            ->willReturn($migrationMock);

        $this->migrationStatusRepository->method('findAll')
            ->willReturn([]);

        $this->assertCount(2, $this->migrationService->findUnexecutedMigrations());
    }

    /**
     * @test
     */
    public function Can_Filter_Executed_Migrations_In_Get_UnexecutedMigrations()
    {
        $migrationMock = $this->getMockBuilder(Migration::class)
            ->getMock();

        $this->objectManager->method('get')
            ->willReturn($migrationMock);

        $migrationStatusMock = $this->getMockBuilder(MigrationStatus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $migrationStatusMock->method('getVersion')
            ->willReturn('1312192');

        $this->migrationStatusRepository->method('findAll')
            ->willReturn([$migrationStatusMock]);

        $migrations = $this->migrationService->findUnexecutedMigrations();

        $this->assertCount(1, $migrations);
        $this->assertArrayHasKey('1312193', $migrations);
    }

    /**
     * @test
     */
    public function Can_Return_A_Single_Migration()
    {
        $migrationMock = $this->getMockBuilder(Migration::class)
            ->getMock();

        $this->objectManager->method('get')
            ->willReturn($migrationMock);

        $migrationStatusMock = $this->getMockBuilder(MigrationStatus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $migrationStatusMock->method('getVersion')
            ->willReturn('1312192');

        $this->migrationStatusRepository->method('findAll')
            ->willReturn([$migrationStatusMock]);

        $migration = $this->migrationService->getMigrationByVersion('1312192');

        $this->assertInstanceOf(Migration::class, $migration);
    }

    /**
     * @test
     */
    public function Will_Throw_Unknown_Migration_If_Migration_Not_Found()
    {
        $this->expectException(UnknownMigration::class);

        $migrationMock = $this->getMockBuilder(Migration::class)
            ->getMock();

        $this->objectManager->method('get')
            ->willReturn($migrationMock);

        $migrationStatusMock = $this->getMockBuilder(MigrationStatus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $migrationStatusMock->method('getVersion')
            ->willReturn('1312192');

        $this->migrationStatusRepository->method('findAll')
            ->willReturn([$migrationStatusMock]);

        $this->migrationService->getMigrationByVersion('1458');
    }
}

class Version1312192 {

}

class Version1312193 {

}
