<?php
namespace FACTFinder\Core\Server;

class Request
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var int
     */
    private $id;

    /**
     * @var ConnectionData
     */
    private $connectionData;

    /**
     * @var AbstractDataProvider
     */
    private $dataProvider;

    public function __construct(
        $loggerClass,
        ConnectionData $connectionData,
        AbstractDataProvider $dataProvider
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->id = $dataProvider->register($connectionData);

        $this->connectionData = $connectionData;
        $this->dataProvider = $dataProvider;
    }

    public function __destruct()
    {
        $this->dataProvider->unregister($this->id);
    }

    /**
     * Returns the parameters object used for the request, on which HTTP header
     * fields can be changed.
     *
     * @return \FACTFinder\Util\Parameters
     */
    public function getHttpHeaderFields()
    {
        return $this->connectionData->getHttpHeaderFields();
    }

    /**
     * Returns the parameters object used for the request, on which parameters
     * can be changed.
     *
     * @return \FACTFinder\Util\Parameters
     */
    public function getParameters()
    {
        return $this->connectionData->getParameters();
    }

    /**
     * Set the action to be queried on the FACT-Finder server. e.g. "Search.ff".
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->connectionData->setAction($action);
    }

    /**
     * @param int $timeout
     */
    public function setConnectTimeout($timeout)
    {
        $this->dataProvider->setConnectTimeout($this->id, $timeout);
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->dataProvider->setTimeout($this->id, $timeout);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        $this->dataProvider->loadResponse($this->id);
        return $this->connectionData->getResponse();
    }
}
