<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Traits;


trait UpdateMatchedContractsTrait
{
    protected $lines = [];

    public function updateMatchedContracts()
    {
        if (!$this->storage->isOnlyException()) {
            return false;
        }

        $this->lines = [];

        $filePath = $this->storage->getFilePath();
        $newFilePath = $this->getNewFilePath();
        $this->copyHeader($newFilePath);
        $self = $this;
        $total = $this->mapping->getTotal();

        $callbackSuccess = function () use ($self, $filePath) {
            $self->removeLastLineInFile($filePath);
        };

        $callbackFailed = function () use ($self, $filePath) {
            $self->moveLine($filePath);
        };

        for ($i = 1; $i <= $total; $i++) {
            $this->updateMatchedContractsWithCallback(
                $callbackSuccess,
                $callbackFailed
            );
        }

        krsort($this->lines);
        file_put_contents($newFilePath, implode('', $this->lines), FILE_APPEND | LOCK_EX);
        $this->storage->setFilePath(basename($newFilePath));

        return true;
    }

    /**
     * @param $newFilePath
     * @return string
     */
    protected function copyHeader($newFilePath)
    {
        $lines = file($this->storage->getFilePath());
        $firstLine = reset($lines);
        file_put_contents($newFilePath, $firstLine, FILE_APPEND | LOCK_EX);

        return $newFilePath;
    }

    /**
     * @return string
     */
    protected function getNewFilePath()
    {
        return sprintf(
            '%s%s.csv',
            $this->storage->getFileDirectory(),
            uniqid()
        );
    }
}
