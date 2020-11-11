<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Domain\Service;

class VersionResolver
{
    public function extractVersion(string $migrationClassName): string
    {
        /*
         *  date format version number:
         *       4 digits year
         *    +  2 digits month
         *    +  2 digits day
         *    +  2 digits hour
         *    +  2 digits minute
         *    +  2 digits second
         *    = 14 digits
         */
        preg_match('#\\\\Version(?<dateFormatVersionNumber>\\d{14})(\\\\|$)#', $migrationClassName, $matches);
        return $matches['dateFormatVersionNumber'];
    }
}
