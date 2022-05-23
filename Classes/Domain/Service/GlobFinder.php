<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Doctrine\Migrations\Finder\Finder;

final class GlobFinder extends Finder
{

    public function findMigrations(string $directory, ?string $namespace = null): array
    {
        $dir = $this->getRealPath($directory);

        $files = glob(rtrim($dir, '/') . '/**/Version*.php');

        return $this->loadMigrations($files, $namespace);
    }

}
