<?php
namespace FACTFinder\Core;

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

    function __construct(
        $loggerClass,
        ConfigurationInterface $configuration,
        AbstractEncodingConverter $encodingConverter
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;
        $this->encodingConverter = $encodingConverter;
    }

    /**
     * Loads parameters from the request and returns them as an array. The keys
     * correspond to parameter names. String values correspond to single values.
     * Array values correspond to multiple values for the same parameter.
     * Also takes care of encoding conversion if necessary.
     *
     * @return mixed[] Array of UTF-8 encoded parameters
     */
    public function getRequestParameters()
    {
        if ($this->requestParameters === null) {
            if (isset($_SERVER['QUERY_STRING'])) {
                $parameters = array_merge(
                    $_POST,
                    $this->parseParametersFromString($_SERVER['QUERY_STRING'])
                );
            } else if (isset($_GET)) {
                // Don't use $_REQUEST, because it also contains $_COOKIE.
                $parameters = array_merge($_POST, $_GET);
                $this->log->warn('$_SERVER[\'QUERY_STRING\' is not available. '
                               . 'Using $_GET instead. This may cause problems '
                               . 'if the query string contains parameters with '
                               . 'spaces or periods.');
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
     * @return mixed[] array of parameter variables
     */
    protected function parseParametersFromString($input)
    {
        if (strpos($input, '?') !== false)
        {
            $parts = explode('?', $input, 2);
            $input = $parts[1];
        }

        $result = array();
        $pairs = explode('&', $input);
        foreach($pairs AS $pair){
            $pair = explode('=', $pair);
            if(empty($pair[0])) continue;
            if(count($pair) == 1) $pair[1] = '';
            $k = $pair[0];
            $v = $pair[1];

            $k = urldecode($k);
            $v = urldecode($v);

            $k = preg_replace('/\[]$/', '', $k);

            if (!isset($result[$k]))
                $result[$k] = $v;
            else
            {
                if (is_array($result[$k]))
                    array_push($result[$k], $v);
                else
                    $result[$k] = array($result[$k], $v);
            }
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
}
