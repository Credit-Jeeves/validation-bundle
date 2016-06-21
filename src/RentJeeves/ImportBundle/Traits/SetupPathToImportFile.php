<?php

namespace RentJeeves\ImportBundle\Traits;

trait SetupPathToImportFile
{
    /**
     * @var string
     */
    protected $pathToFile;

    /**
     * @param string $pathToFile
     */
    public function setPathToFile($pathToFile)
    {
        $this->pathToFile = $pathToFile;
    }
}
