<?php
declare(strict_types=1);


namespace Netlogix\Migrations\Test\s\Unit\Domain\Service;

use Doctrine\DBAL\Migrations\Finder\GlobFinder;
use Neos\Flow\Package;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Migrations\Domain\Model\Migration;
use Netlogix\Migrations\Domain\Service\VersionResolver;

class VersionResolverTest extends UnitTestCase
{
    /**
     * @var VersionResolver
     */
    private $versionResolver;

    protected function setUp()
    {
        parent::setUp();

       $this->versionResolver = new VersionResolver();
    }

    /**
     * @test
     * @dataProvider getProvidedData
     */
    public function Can_extract_version_string(string $migrationClassName, string $expectedVersionString)
    {
        $this->assertEquals($expectedVersionString, $this->versionResolver->extractVersion($migrationClassName));
    }

    public function getProvidedData()
    {
        return [
            ['Version20190930132259', '20190930132259'],
            ['Version20190930132253', '20190930132253'],
        ];
    }
}
