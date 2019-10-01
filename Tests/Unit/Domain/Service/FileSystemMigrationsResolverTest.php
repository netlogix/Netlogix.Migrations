<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Tests\Unit\Domain\Service;
use Doctrine\DBAL\Migrations\Finder\GlobFinder;
use Neos\Flow\Package;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Migrations\Domain\Service\FileSystemMigrationsResolver;

class FileSystemMigrationsResolverTest extends UnitTestCase
{
    /**
     * @var FileSystemMigrationsResolver
     */
    private $fileSystemMigrationsResolver;

    protected function setUp()
    {
        parent::setUp();

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->disableOriginalConstructor()
            ->getMock();


        $dummyPackage = $this->getMockBuilder(Package::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dummyPackage
            ->method('getPackagePath')
            ->willReturn('dummy/package/path/');

        $packageManager->method('getAvailablePackages')
            ->willReturn([$dummyPackage]);

        $this->fileSystemMigrationsResolver = new FileSystemMigrationsResolver($packageManager, new GlobFinder());
    }

    /**
     * @test
     */
    public function Can_return_empty_array()
    {
        $files = $this->fileSystemMigrationsResolver->findMigrationFiles();
        $this->assertCount(0, $files);
    }
}
