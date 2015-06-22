<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;

class AciReportArchiverCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateAciReportArchiverObjectAndObjectInstanceofRightClass()
    {
        $logger = $this->getLoggerMock();
        $aciArchiver = new AciReportArchiver('\\', $logger);

        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciReportArchiver',
            $aciArchiver
        );
    }

    /**
     * @test
     */
    public function shouldArchiveFile()
    {
        $logger = $this->getLoggerMock();
        $pathToFile = $this->getFileLocator()->locate(
            '@RjCheckoutBundle/Tests/Fixtures/Aci/AciReportLoader/testForLoaderAndDownloader.txt'
        );
        $reportPath = substr($pathToFile, 0, strripos($pathToFile, '/'));

        $aciArchiver = new AciReportArchiver($reportPath, $logger);
        $aciArchiver->archive($pathToFile);

        $now = new \DateTime();
        $archivePath = sprintf('%s/%s/%s/%s', $reportPath, 'archive', $now->format('Y'), $now->format('m'));

        $file = scandir($archivePath)[2];

        //Delete new dirs and move file
        $filesystem = new Filesystem();
        $filesystem->rename($archivePath . '/' . $file, $pathToFile);
        $filesystem->remove(sprintf('%s/%s/%s', $reportPath, 'archive', $now->format('Y')));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
