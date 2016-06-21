<?php
namespace RentJeeves\DataBundle\FixtureGenerator\Services;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class FixtureFinder
{
    /** @var  KernelInterface $kernel */
    protected $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Return array with fixtures' filename
     *
     * @param string $path
     * @param array $files
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getFixtures($path, array $files = null)
    {
        $fixtures = [];
        if (true === empty($files)) {
            $fixtures = $this->getFixturesFromDirectory($this->getPathWithFixtures($path));
        } else {
            $path = $this->getPathWithFixtures($path);
            foreach ($files as $file) {
                if (!file_exists($path.$file)) {
                    throw new \InvalidArgumentException(sprintf('Fixture file does not exist: %s', $path.$file));
                }
                $fixtures[] = $path.$file;
            }
        }
        if (0 === count($fixtures)) {
            throw new \InvalidArgumentException(sprintf('Could not find any fixtures to load'));
        }

        return $fixtures;
    }

    /**
     * @param string $absolutePath
     * @return array
     */
    protected function getFixturesFromDirectory($absolutePath)
    {
        $fixtures = [];
        $finder = Finder::create()->depth(0)->files()->in($absolutePath)->name('*.yml')->sortByName();
        foreach ($finder as $file) {
            /* @var SplFileInfo $file */
            $fixtures[$file->getRealPath()] = true;
        }

        return array_keys($fixtures);
    }

    /**
     * @param $path
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getPathWithFixtures($path)
    {
        try {
            return $this->kernel->locateResource($path);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Path not found: %s', $path));
        }
    }
}
