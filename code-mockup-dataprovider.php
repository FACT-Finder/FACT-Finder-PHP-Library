<?php

interface RequestFactoryInterface
{
    /**
     * @returns Request
     */
    public function getRequest();
}

class EasyCurlRequestFactory implements RequestFactoryInterface
{
    private $dataProvider = new EasyCurlDataProvider();

    public function getRequest()
    {
        $request = new CurlRequest($dataProvider);
        $this->dataProvider->register($request);
    }
}

abstract class AbstractDataProvider
{
    protected $urlBuilder = new UrlBuilder();

    private $requests = array();
    private $responses = array();

    public function register(Request $request)
    {
        $this->requests[$request->id] = $request;
        $this->responses[$request->id] = null;
    }

    public function getResponse($request)
    {
        $response = $this->$responses[$request];
        if (is_null($response) || $this->hasUrlChanged($request))
            $this->$responses[$request->id] = $this->loadResponse($request);

        return $this->$responses[$request->id];
    }

private function hasUrlChanged($request)
    {
        // uses $request->getPreviousUrl() and $this->urlBuilder
    }

    /**
     * @return Response
     */
    abstract protected function loadResponse($request);
}

class EasyCurlDataProvider extends AbstractDataProvider
{
    protected function loadResponse($id)
    {
        //...
    }
}

class Request
{
    private static $nextId = 0;

    private $id;
    private $dataProvider;

    protected $httpHeaderFields;
    protected $action;
    protected $parameters;
    protected $connectTimeout;
    protected $timeout;
    protected $connectionOptions;
    protected $previousUrl;
    protected $response;

    public function __construct($dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->id = self::$nextId++;
    }

    // for everyone
    public function getHttpHeaderFields() { return $this->httpHeaderFields; }
    public function getParameters() { return $this->parameters; }

    // for Adapters
    public function setAction($action) { $this->action = $action; }
    public function setConnectTimeout($timeout) { $this->connectTimeout = $timeout; }
    public function setTimeout($timeout) { $this->timeout = $timeout; }
    public function getResponse() { return $this->dataProvider->getResponse($this->id); }

    // for DataProvider
    public function getAction() { return $this->action; }
    public function isResponseSet() { return empty($this->response); }
    public function setReponse($response) { $this->response = $response; }
    public function getPreviousUrl() { return $this->previousUrl; }
    public function getConnectTimeout() { return $this->connectTimeout; }
    public function getTimeout() { return $this->timeout; }
    public function getConnectionOptions() { return $this->connectionOptions; }
    public function setConnectionOptions($co) { array_merge($this->connectionOptions, $co); }
}
