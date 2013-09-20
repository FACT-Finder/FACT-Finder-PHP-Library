<?php
namespace FACTFinder\Core\Server;

class Response
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var int
     */
    private $connectionErrorNumber;

    /**
     * @var string
     */
    private $connectionError;

    public function __construct(
        $content,
        $httpCode,
        $connectionErrorNumber,
        $connectionError
    ) {
        $this->content = $content;
        $this->httpCode = $httpCode;
        $this->connectionErrorNumber = $connectionErrorNumber;
        $this->connectionError = $connectionError;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return int
     */
    public function getConnectionErrorNumber()
    {
        return $this->connectionErrorNumber;
    }

    /**
     * @return string
     */
    public function getConnectionError()
    {
        return $this->connectionError;
    }
}
