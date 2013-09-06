<?php
namespace FACTFinder\Core;

use FACTFinder\Loader as FF;

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
     * @var FACTFinder\Util\Parameters
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $action;


    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *        implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $configuration
     * @param FACTFinder\Util\Parameters $parameters Optional parameters object
     *        to initialize the UrlBuilder with.
     */
    public function __construct(
        $loggerClass,
        ConfigurationInterface $configuration,
        FACTFinder\Util\Parameters $parameters = null
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->log->info("Initializing URL Builder.");

        $this->configuration = $configuration;

        $this->parameters = $parameters ?: FF::getInstance('Util\Parameters');
    }

    /**
     * Set the action to be queried on the FACT-Finder server. e.g. "Search.ff"
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Get the action to be queried on the FACT-Finder server. e.g. "Search.ff"
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns the parameters object used by the UrlBuilder, on which parameters
     * can be changed.
     *
     * @return FACTFinder\Util\Parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns a full URL with authentication data. The type of authentication
     * is determined from the configuration.
     * Note that this method may set a channel parameter if there is none
     * already.
     *
     * @return string The full URL.
     *
     * @throws Exception if no valid authentication type was configured.
     */
    public function getAuthenticationUrl()
    {
        $this->ensureChannelParameter();

        $c = $this->configuration;
        if ($c->isAdvancedAuthenticationType())
            return $this->getAdvancedAuthenticationUrl();
        else if ($c->isSimpleAuthenticationType())
            return $this->getSimpleAuthenticationUrl();
        else if ($c->isHttpAuthenticationType())
            return $this->getHttpAuthenticationUrl();
        else
            throw new \Exception('Invalid authentication type configured.');
    }

    /**
     * Get URL with advanced authentication encryption.
     *
     * @return string The full URL.
     */
    protected function getAdvancedAuthenticationUrl()
    {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $ts         = time() . '000'; //milliseconds needed
        $prefix     = $configuration->getAuthenticationPrefix();
        $postfix    = $configuration->getAuthenticationPostfix();
        $hashedPW   = md5($prefix
                    . $ts
                    . md5($configuration->getPassword())
                    . $postfix);
        $authenticationParameters = 'timestamp=' . $ts
                                  . '&username=' . $configuration->getUserName()
                                  . '&password=' . $hashedPW;

        $url = $this->buildAddress()
             . '?' . $parameters->toJavaQueryString()
             . (count($parameters) ? '&' : '') . $authenticationParameters;

        $this->log->info("Request Url: " . $url);
        return $url;
    }

    /**
     * Get URL with simple authentication encryption.
     *
     * @return string The full URL.
     */
    protected function getSimpleAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $ts = time() . '000'; //milliseconds needed but won't be considered
        $authenticationParameters = "timestamp=" . $ts
                        . '&username=' . $configuration->getUserName()
                        . '&password=' . md5($configuration->getPassword());

        $url = $this->buildAddress()
             . '?' . $parameters->toJavaQueryString()
             . (count($parameters) ? '&' : '') . $authenticationParameters;

        $this->log->info("Request Url: " . $url);
        return $url;
    }

    /**
     * Get URL with HTTP authentication.
     *
     * @return string The full URL.
     */
    protected function getHttpAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $authentication = sprintf(
            '%s:%s@',
            $configuration->getUserName(),
            $configuration->getPassword()
        );
        if ($authentication == ':@') $authentication = '';

        $url = $this->buildAddress(true)
             . (count($parameters) ? '?' : '') . $parameters->toJavaQueryString();

        $this->log->info("Request Url: " . $url);
        return $url;
    }

    /**
     * Get URL without authentication data.
     *
     * @return string The full URL.
     */
    public function getNonAuthenticationUrl() {
        $configuration = $this->configuration;
        $parameters = $this->parameters;

        $this->ensureChannelParameter();

        $url = $this->buildAddress()
             . (count($parameters) ? '?' : '') . $parameters->toJavaQueryString();

        return $url;
    }

    /**
     * If no channel is set, try to fill it from configuration data.
     */
    protected function ensureChannelParameter() {
        if ((!isset($this->parameters['channel'])
            || $this->parameters['channel'] == '')
            && $this->configuration->getChannel() != ''
        ) {
            $this->parameters['channel'] = $this->configuration->getChannel();
        }
    }

    protected function buildAddress($includeHttpAuthentication = false)
    {
        $configuration = $this->configuration;

        $authentication = '';
        if ($includeHttpAuthentication
            && $configuration->getUserName() != ''
            && $configuration->getPassword() != ''
        ) {
            $authentication = sprintf(
                '%s:%s@',
                $configuration->getUserName(),
                $configuration->getPassword()
            );
        }

        return $configuration->getRequestProtocol() . '://'
             . $authentication . $configuration->getServerAddress()
             . ':' . $configuration->getServerPort()
             . '/' . $configuration->getContext()
             . '/' . $this->action;
    }
}
