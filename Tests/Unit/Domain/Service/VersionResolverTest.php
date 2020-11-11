<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Tests\Unit\Domain\Service;

use Neos\Flow\Tests\UnitTestCase;
use Netlogix\Migrations\Domain\Service\VersionResolver;
use Netlogix\Migrations\Error\InvalidClassName;

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
     * @dataProvider validClassNames
     */
    public function Can_extract_version_string(string $migrationClassName, string $expectedVersionString)
    {
        $this->assertEquals($expectedVersionString, $this->versionResolver->extractVersion($migrationClassName));
    }

    /**
     * @test
     * @dataProvider invalidClassNames
     */
    public function Can_not_extract_version_string(string $migrationClassName)
    {
        $this->expectException(InvalidClassName::class);
        $this->versionResolver->extractVersion($migrationClassName);
    }

    public function invalidClassNames()
    {
        return [
            'lower case class name' => ['version20201111145100'],
            'to many digits' => ['Version202011111451001'],
            'to little digits' => ['version2020111114510'],
        ];
    }

    public function validClassNames()
    {
        return [
            'class name without namespace' => ['Version20190930132259', '20190930132259'],
            ['Version20190930132253', '20190930132253'],
            'class name with namespace' => ['Migrations\\Version20201111143501', '20201111143501'],
            'version number in namespace' => ['Migrations\\Version20201111143502\\MyClass', '20201111143502'],
        ];
    }
}
