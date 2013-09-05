<?php
namespace FACTFinder\Core;

/**
 * Assembles URLs for different kinds of authentication based on the given
 * parameters and the configuration.
 */
class UrlBuilder
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var mixed[]
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $action;


    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *        implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration
     * @param mixed[] $parameters Optional parameters array to initialize the
     *        UrlBuild with.
     */
    public function __construct(
        $loggerClass,
        ConfigurationInterface $configuration,
        array $parameters = null
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->log->info("Initializing URL Builder.");

        $this->configuration = $configuration;
        if ($parameters != null) $this->parameters = $parameters;
    }

    /**
     * Reset all parameters
     *
     * @param mixed[] $parameters
     */
    public function resetParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Reset a single parameter
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Set all given parameters. Previous values of the given parameters will be
     * replaced. Unmentioned parameters will retain their values.
     *
     * @param mixed[] $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Adds a single parameter. If there it already has one or more values, the
     * new one will be added in addition to the old one(s).
     *
     * @param string $name
     * @param mixed $value
     */
    public function addParameter($name, $value)
    {
        if(!isset($this->parameters[$name]))
            $this->parameters[$name] = $value;
        else
        {
            if (is_array($this->parameters[$name]))
                array_push($this->parameters[$name], $value);
            else
                $this->parameters[$name] = array(
                    $this->parameters[$name],
                    $value
                );
        }
    }

    /**
     * Adds all given parameters in addition to all existing values.
     *
     * @param mixed[] $parameters
     */
    public function addParameters(array $parameters)
    {
        foreach ($parameters as $k => $v)
            $this->addParameter($k, $v);
    }

    /**
     * Unset a single parameter
     *
     * @param string $name
     */
    public function unsetParameter($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * Unset all parameters. This is just a convenience method and could also be
     * done with resetParameters().
     */
    public function unsetAllParameters($name)
    {
        $this->resetParameters(array());
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * get url with advanced authentication encryption
     *
     * @return string url
     */
    public function getAdvancedAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $channel = $this->getChannel($parameters, $configuration);
        if ($channel != '') {
            $parameters['channel'] = $channel;
        }

        $ts         = time() . '000'; //milliseconds needed
        $prefix     = $configuration->getAuthenticationPrefix();
        $postfix    = $configuration->getAuthenticationPostfix();
        $authParameters = "timestamp=$ts&username=".$configuration->getUserName()
            . '&password=' . md5($prefix . $ts . md5($configuration->getPassword()) . $postfix);

        $url = $configuration->getRequestProtocol() . '://'
            . $configuration->getServerAddress() . ':' . $configuration->getServerPort() . '/'
            . $configuration->getContext() . '/'.$this->action.'?' . http_build_query($parameters, '', '&')
            . (count($parameters)?'&':'') . $authParameters;

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with simple authentication encryption
     *
     * @return string url
     */
    public function getSimpleAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $channel = $this->getChannel($parameters, $configuration);
        if ($channel != '') {
            $parameters['channel'] = $channel;
        }

        $ts = time() . '000'; //milliseconds needed but won't be considered
        $authParameters = "timestamp=$ts&username=".$configuration->getUserName()
            . '&password=' . md5($configuration->getPassword());

        $url = $configuration->getRequestProtocol() . '://'
            . $configuration->getServerAddress() . ':' . $configuration->getServerPort() . '/'
            . $configuration->getContext() . '/'.$this->action.'?' . http_build_query($parameters, '', '&')
            . (count($parameters)?'&':'') . $authParameters;

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with http authentication
     *
     * @return string url
     */
    public function getHttpAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $channel = $this->getChannel($parameters, $configuration);
        if ($channel != '') {
            $parameters['channel'] = $channel;
        }

        $auth = $configuration->getUserName() . ':' . $configuration->getPassword() . '@';
        if ($auth == ':@') $auth = '';

        $url = $configuration->getRequestProtocol() . '://' . $auth
            . $configuration->getServerAddress() . ':' . $configuration->getServerPort() . '/'
            . $configuration->getContext() . '/' . $this->action . (count($parameters)?'?':'')
            . http_build_query($parameters, '', '&');

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with no authentication.
     *
     * @return string url
     */
    public function getNonAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $channel = $this->getChannel($parameters, $configuration);
        if ($channel != '') {
            $parameters['channel'] = $channel;
        }

        $url = $configuration->getRequestProtocol() . '://'
            . $configuration->getServerAddress() . ':' . $configuration->getServerPort() . '/'
            . $configuration->getContext() . '/' . $this->action . (count($parameters)?'?':'')
            . http_build_query($parameters, '', '&');

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        // Include the following line only for debugging purposes
        // This method is called quite often for several checking tasks
        //$this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get channel from parameters or configuration (parameters override configuration)
     *
     * @param array $parameters
     * @param FACTFinder_Abstract_Configuration $configuration
     * @return string channel
     */
    protected function getChannel($parameters, $configuration) {
        $channel = '';
        if (isset($parameters['channel']) && strlen($parameters['channel']) > 0) {
            $channel = $parameters['channel'];
        } else if($configuration->getChannel() != '') {
            $channel = $configuration->getChannel();
        }
        return $channel;
    }
}
