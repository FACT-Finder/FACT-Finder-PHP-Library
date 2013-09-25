<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class EasyCurlRequestFactoryTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\EasyCurlRequestFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->curlStub = self::$dic['curlStub'];
        $this->factory = FF::getInstance(
            'Core\Server\EasyCurlRequestFactory',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['curlStub']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];
    }

    public function testGetWorkingRequest()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=json&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent = 'test response';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent, $requiredOptions);
        $this->curlStub->setInformation($info, $requiredOptions);

        $request = $this->factory->getRequest();

        $parameters = $request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $request->setAction('TagCloud.ff');

        $response = $request->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent, $response->getContent());
    }
}
