<?php
namespace FACTFinder\Core\Client;

use FACTFinder\Loader as FF;

class RequestParser
{
    protected $requestParameters;
    protected $requestTarget;

    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var AbstractEncodingConverter
     */
    protected $encodingConverter;


    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *                            implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration
     * @param AbstractEncodingConverter $encodingConverter
     */
    function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;
        $this->encodingConverter = $encodingConverter;
    }

    /**
     * Loads parameters from the request and returns a Parameter object.
     * Also takes care of encoding conversion if necessary.
     *
     * @return Parameters Array of UTF-8 encoded parameters
     */
    public function getRequestParameters()
    {
        if ($this->requestParameters === null) {
            if (isset($_SERVER['QUERY_STRING'])) {
                $parameters = $this->parseParametersFromString($_SERVER['QUERY_STRING']);
                $parameters->setAll($_POST);
            } else if (isset($_GET)) {
                $this->log->warn('$_SERVER[\'QUERY_STRING\'] is not available. '
                               . 'Using $_GET instead. This may cause problems '
                               . 'if the query string contains parameters with '
                               . 'spaces or periods.');

                // Don't use $_REQUEST, because it also contains $_COOKIE.
                $parameters = FF::getInstance(
                    'Util\Parameters',
                    array_merge($_POST, $_GET)
                );
            } else {
                // For CLI use:
                $parameters = array();
            }

            $this->requestParameters = $this->encodingConverter
                                            ->decodeClientUrlData($parameters);
        }
        return $this->requestParameters;
    }

    /**
     * Extracts a parameter array with name => value pairs from a URL or a query
     * string.
     * Also takes care of URL decoding.
     *
     * @param string query string or URL
     * @return Parameters array of parameter variables
     */
    protected function parseParametersFromString($input)
    {
        if (strpos($input, '?') !== false)
        {
            $parts = explode('?', $input, 2);
            $input = $parts[1];
        }

        $result = FF::getInstance('Util\Parameters');
        $pairs = explode('&', $input);
        foreach($pairs AS $pair){
            $pair = explode('=', $pair);
            // Make sure that the parameter name actually contains an identifier
            if(preg_match('/^(?:$|\[[^\]]*\])/', $pair[0]))
                continue;
            if(count($pair) == 1)
                $pair[1] = '';

            $k = urldecode($pair[0]);
            $v = urldecode($pair[1]);

            if (preg_match('/^[^\]]+(?=\[[^\]]*\])/', $k, $matches))
                $result->add($matches[0], $v);
            else
                $result[$k] = $v;
        }
        return $result;
    }

    /**
     * Get target of the current request.
     *
     * @return string request target
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget === null)
        {
            // Workaround for some servers (IIS) which do not provide
            // $_SERVER['REQUEST_URI']. Taken from
            // http://php.net/manual/en/reserved.variables.server.php#108186
            if(!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
                if($_SERVER['QUERY_STRING']) {
                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            if (strpos($_SERVER['REQUEST_URI'], '?') === false)
                $this->requestTarget = $_SERVER['REQUEST_URI'];
            else
            {
                $parts = explode('?', $_SERVER['REQUEST_URI']);
                $this->requestTarget = $parts[0];
            }
        }
        return $this->requestTarget;
    }

    public function setRequestTarget($target)
    {
        $this->requestTarget = $target;
    }
}
