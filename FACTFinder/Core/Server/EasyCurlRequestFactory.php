<?php
namespace FACTFinder\Core\Server;

use \FACTFinder\Loader as FF;

/**
 * This implementation backs the Request with an EasyCurlDataProvider.
 */
class EasyCurlRequestFactory implements RequestFactoryInterface
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

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Util\CurlInterface $curl
    ) {
        $this->loggerClass = $loggerClass;
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;

        $urlBuilder = FF::getInstance('Core\Server\UrlBuilder',
            $loggerClass,
            $configuration
        );
        $this->dataProvider = FF::getInstance('Core\Server\EasyCurlDataProvider',
            $loggerClass,
            $configuration,
            $curl,
            $urlBuilder
        );
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
