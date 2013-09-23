<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class EasyCurlDataProviderTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Util\CurlStub
     */
    protected $curlStub;

    /**
     * @var FACTFinder\Core\Server\EasyCurlDataProvider
     */
    protected $dataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->curlStub = FF::getInstance('Util\CurlStub');
        $this->dataProvider = FF::getInstance(
            'Core\Server\EasyCurlDataProvider',
            $this->dic['loggerClass'],
            $this->dic['configuration'],
            $this->curlStub,
            $this->dic['urlBuilder']
        );

        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = $this->dic['configuration'];
    }

    public function testLoadResponse()
    {
        $this->configuration->setAuthenticationType('http');

        $requiredOptions = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=json&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent = 'test response';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent, $requiredOptions);
        $this->curlStub->setInformation($info, $requiredOptions);

        $connectionData = FF::getInstance('Core\Server\ConnectionData');
        $id = $this->dataProvider->register($connectionData);

        $parameters = $connectionData->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $connectionData->setAction('TagCloud.ff');

        $this->dataProvider->loadResponse($id);

        $response = $connectionData->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent, $response->getContent());
    }
}
