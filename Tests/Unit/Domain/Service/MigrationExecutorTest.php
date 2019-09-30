<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Test\s\Unit\Domain\Service;

use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Migrations\Domain\Handler\MigrationHandler;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Domain\Service\MigrationExecutor;
use Netlogix\Migrations\Domain\Service\VersionLogger;
use Netlogix\Migrations\Error\MissingMigrationHandler;

class MigrationExecutorTest extends UnitTestCase
{
    /**
     * @var MigrationExecutor
     */
    private $migrationExecutor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|VersionLogger
     */
    private $versionLogger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ReflectionService
     */
    private $reflectionService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->versionLogger = $this->getMockBuilder(VersionLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reflectionService = $this->getMockBuilder(ReflectionService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->migrationExecutor = new MigrationExecutor(
            $this->reflectionService,
            $this->objectManager,
            $this->versionLogger
        );
    }

    /**
     * @test
     */
    public function It_Will_Throw_Missing_Migration_Handler()
    {
        $this->expectException(MissingMigrationHandler::class);

        $migration = $this->createMock(Migration::class);

        $this->reflectionService->method('getAllImplementationClassNamesForInterface')
            ->willReturn([]);

        $this->migrationExecutor->execute($migration);
    }

    /**
     * @test
     */
    public function It_Will_Execute_Migration_Handler()
    {
        $migration = $this->createMock(Migration::class);

        $migrationHandler = $this->getMockBuilder(MigrationHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $migrationHandler->expects($this->once())
            ->method('canExecute')
            ->willReturn(true);

        $migrationHandler->expects($this->once())
            ->method('up')
            ->with($migration);

        $this->reflectionService->method('getAllImplementationClassNamesForInterface')
            ->willReturn(['TestPackage\Subpackage\Class1']);

        $this->objectManager->method('get')
            ->with('TestPackage\Subpackage\Class1')
            ->willReturn($migrationHandler);

        $this->migrationExecutor->execute($migration);
    }
}
