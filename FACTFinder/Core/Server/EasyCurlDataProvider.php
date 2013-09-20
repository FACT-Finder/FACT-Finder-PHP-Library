<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * This implementation retrieves the FACT-Finder data by using the "easy cURL
 * interface" (as opposed to the multi-cURL interface). Responses are queried
 * sequentially and lazily and are cached as long as parameters don't change.
 */
class EasyCurlDataProvider extends AbstractDataProvider
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \FACTFinder\Util\CurlInterface
     */
    protected $curl;

    protected $defaultCurlOptions;
    protected $necessaryCurlOptions;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Util\CurlInterface $curl
    ) {
        parent::__construct($loggerClass, $configuration);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->urlBuilder = FF::getInstance('Core\Server\UrlBuilder');

        $this->curl = $curl;

        $this->defaultCurlOptions = array(
            CURLOPT_CONNECTTIMEOUT => $this->configuration->getDefaultConnectTimeout(),
            CURLOPT_TIMEOUT        => $this->configuration->getDefaultTimeout(),
        );

        $this->necessaryCurlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_SSL_VERIFYPEER => false,
            //CURLOPT_SSL_VERIFYHOST => false,
        );
    }

    public function setConnectTimeout($id, $timeout)
    {
        $this->connectionData[$handle]->setConnectionOption(
            CURLOPT_CONNECTTIMEOUT,
            $timeout
        );
    }

    public function setTimeout($handle, $timeout);
    {
        $this->connectionData[$handle]->setConnectionOption(
            CURLOPT_TIMEOUT,
            $timeout
        );
    }

    // TODO: Split up into smaller methods.
    public function loadResponse($id)
    {
        if (!isset($this->connectionData[$id]))
            throw new \InvalidArgumentException('Tried to get response for invalid ID $id.');

        if (!$this->hasUrlChanged($id))
            return;

        $connectionData = $this->connectionData[$id];

        $action = $connectionData->getAction();
        if (empty($action))
        {
            $this->log->error('Request type missing.');
            $connectionData->setNullResponse();
            return;
        }

        $httpHeaderFields = clone $connectionData->getHttpHeaderFields();
        $parameters = clone $connectionData->getParameters();

        $language = $this->configuration->getLanguage();
        if (!empty($language))
            $httpHeaderFields['Accept-Language'] = $language;

        if ($this->configuration->isDebugEnabled())
        {
            $parameters['verbose'] = 'true';
            if (isset($_SERVER['HTTP_REFERER'])
                && !$connectionData->issetConnectionOptions(CURLOPT_REFERER)
            ) {
                $connectionData->setConnectionOption(
                    CURLOPT_REFERER,
                    $_SERVER['HTTP_REFERER']
                );
            }
        }

        $connectionData->setConnectionOption(
            CURLOPT_HTTPHEADER,
            $httpHeaderFields->toHttpHeaderFields()
        );

        $url = $this->urlBuilder->getAuthenticationUrl(
            $action,
            $parameters
        );

        $connectionData->setConnectionOption(CURLOPT_URL, $url);

        $curlHandle = $this->curl->init();
        if (!$curlHandle)
        {
            $error = $this->curl->error();
            $this->log->error("curl_init() returned an error for request ID $id: $id. "
                            . 'Setting an empty response...');
            $connectionData->setNullResponse();
            return;
        }

        $this->curl->setopt_array(
            $curlHandle,
            array_merge(
                $this->defaultCurlOptions,
                $connectionData->getConnectionOption(),
                $this->necessaryCurlOptions
            )
        );

        $responseText = $this->curl->exec($curlHandle);
        $httpCode = (int)$this->curl->getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $curlErrorNumber = $this->curl->errno($curlHandle);
        $curlError = $this->curl->error($curlHandle);

        $this->curl->close($curlHandle);

        if ($httpCode() >= 400) {
            $this->log->error("Connection failed. HTTP code: $httpCode");
        } else if ($this->httpCode == 0) {
            $this->log->error("Connection refused. cURL error: $curlError");
        } else if (floor($httpCode / 100) == 2) { // all successful status codes (2**)
            $this->log->info("Request successful!");
        }

        $response = FF::getInstance('Core\Server\Response',
            $responseText,
            $httpCode,
            $curlErrorNumber,
            $curlError
        );

        $connectionData->setResponse($response);
    }

    private function hasUrlChanged($id)
    {
        $connectionData = $this->connectionData[$id];
        $url = $this->urlBuilder->getNonAuthenticationUrl(
            $connectionData->getAction(),
            $connectionData->getParameters()
        );
        return $url != $this->previousUrl;
    }
}
