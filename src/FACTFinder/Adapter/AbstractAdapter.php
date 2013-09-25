<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

abstract class AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var FACTFinder\Core\Server\Request
     */
    protected $request;

    /**
     * @var FACTFinder\Core\ParametersConverter
     */
    protected $parametersConverter;

    /**
     * @var FACTFinder\Util\ContentProcessorInterface
     */
    private $responseContentProcessor;

    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *        implement FACTFinder\Util\LoggerInterface.
     * @param FACTFinder\Core\ConfigurationInterface $configuration
     *        Configuration object to use.
     * @param FACTFinder\Core\Server\Request $request The request object from
     *        which to obtain the server data.
     * @param FACTFinder\Core\ParametersConverter $parametersConverter
     *        Parameters converter object to use.
     */
    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\ParametersConverter $parametersConverter
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);
        $this->configuration = $configuration;
        $this->request = $request;
        $this->parametersConverter = $parametersConverter;

        $this->usePassthroughResponseContentProcessor();
    }

    protected function usePassthroughResponseContentProcessor()
    {
        $this->responseContentProcessor = function($string) {
            return $string;
        };
    }

    protected function useJsonResponseContentProcessor()
    {
        $this->responseContentProcessor = function($string) {
            $jsonData = json_decode($string);

            if (is_null($jsonData))
                throw new InvalidArgumentException(
                    "json_decode() raised an error: ".json_last_error()
                );

            return $jsonData;
        };
    }

    protected function useXmlResponseContentProcessor()
    {
        $this->responseContentProcessor = function($string) {
            libxml_use_internal_errors(true);
            // The constructor throws an exception on error
            return new SimpleXMLElement($string);
        };
    }

    /**
     * Pass in a function to process the response content. This method is not
     * used within the library, but may be convenient when writing custom
     * adapters.
     *
     * @param object $callable A function (or invokable object) that processes
     *        a single string parameter.
     *
     * @throws InvalidArgumentException if $callable is not callable.
     */
    protected function useResponseContentProcessor($callable)
    {
        // Check shamelessly stolen from Pimple.php
        if (!method_exists($callable, '__invoke'))
            throw new \InvalidArgumentException('Content processor is neither a Closure or invokable object.');

        $this->responseContentProcessor = $callable;
    }

    protected function getResponseContent()
    {
        $content = $this->request->getResponse()->getContent();

        // PHP does not (yet?) support $this->method($args) for callable
        // properties
        return $this->responseContentProcessor->__invoke($content);
    }
 }