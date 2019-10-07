<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

use Doctrine\DBAL\Migrations\Finder\AbstractFinder;

final class GlobFinder extends AbstractFinder
{

    public function findMigrations($directory, $namespace = null)
    {
        $dir = $this->getRealPath($directory);

        $files = glob(rtrim($dir, '/') . '/**/Version*.php');

        return $this->loadMigrations($files, $namespace);
    }

}
