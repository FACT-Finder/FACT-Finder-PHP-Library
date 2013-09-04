<?php
namespace FACTFinder\Core;

/**
 * Handles the conversion of parameters between the client url, the links on
 * within client content and requests to the FACT-Finder server.
 */
class ParametersConverter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *                            implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $config Configuration object to use.
     */
    public function __construct(
        $loggerClass,
        ConfigurationInterface $config
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->config = $config;
    }

    /**
     * @param mixed[] $clientParameters Associative array of input parameters.
     *        String keys represent the parameter name, string values represent
     *        single parameter values while string array values represent
     *        multiple parameter values for a single key.
     * @return mixed[] Associative array of parameters ready for use with
     *         FACT-Finder.
     */
    public function convertClientToServerParameters($clientParameters)
    {
        $result = $clientParameters;
        $this->applyParameterMappings($result, $this->config->getServerMappings());
        $this->removeIgnoredParameters($result, $this->config->getIgnoredServerParameters());
        $this->ensureChannelParameter($result);
        $this->addRequiredParameters($result, $this->config->getRequiredServerParameters());

        return $result;
    }

    /**
     * @param mixed[] $serverParameters Associative array of input parameters.
     *        String keys represent the parameter name, string values represent
     *        single parameter values while string array values represent
     *        multiple parameter values for a single key.
     * @return mixed[] Associative array of parameters ready for use with
     *         in requests to the client.
     */
    public function convertServerToClientParameters($serverParameters)
    {
        $result = $serverParameters;
        $this->applyParameterMappings($result, $this->config->getClientMappings());
        $this->removeIgnoredParameters($result, $this->config->getIgnoredClientParameters());
        $this->addRequiredParameters($result, $this->config->getRequiredClientParameters());

        return $result;
    }

    /**
     * Changes the keys in an array of parameters according to the given mapping
     * rules.
     * @param mixed[] &$parameters Parameters to be modified.
     * @param string[] $mappingRules Associative array of mapping rules.
     *        Parameter names will be mapped from keys to values of this array.
     */
    protected function applyParameterMappings(&$parameters, $mappingRules)
    {
        foreach ($mappingRules as $k => $v)
        {
            if ($k != $v && isset($parameters[$k]))
            {
                $parameters[$v] = $parameters[$k];
                unset($parameters[$k]);
            }
        }
    }

    /**
     * Removes keys from an array of parameters according to the given ignore
     * rules. It basically turns the parameters into the set difference of the
     * parameters and the ignore rules based on keys.
     * @param mixed[] &$parameters Parameters to be modified.
     * @param bool[] $ignoreRules Array of parameters to be ignored. The keys
     *        are the parameter names, the values are simply "true", but could
     *        technically have any value.
     */
    protected function removeIgnoredParameters(&$parameters, $ignoreRules)
    {
        foreach ($ignoreRules as $k => $v)
            unset($parameters[$k]);
    }

    /**
     * Ensures that the passed parameters array has a "channel" parameter by
     * adding one if necessary. If the parameter exists but has multiple values,
     * all but the first are discarded.
     */
    protected function ensureChannelParameter(&$parameters)
    {
        if (isset($parameters['channel']) && is_array($parameters['channel']))
            $parameters['channel'] = $parameters['channel'][0];
        if (!isset($parameters['channel']) || strlen($parameters['channel']) == 0)
            $parameters['channel'] = $this->config->getChannel();
    }

    /**
     * Adds keys to an array of parameters according to the given require rules.
     * @param mixed[] &$parameters Parameters to be modified.
     * @param string[] $ignoreRules Array of required parameters. The keys are
     *        the names of the required parameter, the values are default values
     *        to be used if the parameter is not present.
     */
    protected function addRequiredParameters(&$parameters, $requireRules)
    {
        foreach ($requireRules as $k => $v)
            if (!isset($parameters[$k]))
                $parameters[$k] = $v;
    }
}
