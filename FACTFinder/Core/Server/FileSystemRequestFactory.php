<?php
namespace FACTFinder\Core\Server;

use \FACTFinder\Loader as FF;

/**
 * This implementation backs the Request with a FileSystemDataProvider.
 */
class FileSystemRequestFactory implements RequestFactoryInterface
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;
    private $loggerClass;

    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var FileSystemDataProvider
     */
    private $dataProvider;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration
    ) {
        $this->loggerClass = $loggerClass;
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;

        $this->dataProvider = FF::getInstance('Core\Server\FileSystemDataProvider',
            $loggerClass,
            $configuration
        );
    }

    public function setFileLocation($path)
    {
        $this->dataProvider->setFileLocation($path);
    }

    /**
     * Returns a request object all wired up and ready for use.
     * @return Request
     */
    public function getRequest()
    {
        return FF::getInstance('Core\Server\Request',
            $this->loggerClass,
            FF::getInstance('Core\Server\ConnectionData'),
            $this->dataProvider
        );
    }
}
