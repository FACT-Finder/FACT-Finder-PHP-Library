<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class MultiCurlRequestFactoryTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\MultiCurlRequestFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->curlStub = self::$dic['curlStub'];
        $this->factory = FF::getInstance(
            'Core\Server\MultiCurlRequestFactory',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            FF::getInstance('Util\Parameters', array('query' => 'bmx')),
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
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?query=bmx&format=json&do=getTagCloud&verbose=true&channel=de'
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

    // This is how multiple requests should be used: configure all of them
    // before fetching the first response from one of them.
    public function testUseRequestsInParallel()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions1 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?query=bmx&format=json&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent1 = 'test response 1';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent1, $requiredOptions1);
        $this->curlStub->setInformation($info, $requiredOptions1);

        $requiredOptions2 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?query=bmx&format=xml&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent2 = 'test response 2';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent2, $requiredOptions2);
        $this->curlStub->setInformation($info, $requiredOptions2);

        $request1 = $this->factory->getRequest();

        $parameters = $request1->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $request1->setAction('TagCloud.ff');

        $request2 = $this->factory->getRequest();

        $parameters = $request2->getParameters();

        $parameters['format'] = 'xml';
        $parameters['do'] = 'getTagCloud';

        $request2->setAction('TagCloud.ff');

        // This should load the content for the second request as well
        $response = $request1->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent1, $response->getContent());

        $response = $request2->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent2, $response->getContent());
    }

    // This is NOT how the MultiCurlRequestFactory should be used in production.
    // In fact, it will log a warning whenever it is used like this. If
    // possible, always configure all Requests first and then use getResponse().
    public function testUseRequestsInSequence()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions1 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?query=bmx&format=json&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent1 = 'test response 1';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent1, $requiredOptions1);
        $this->curlStub->setInformation($info, $requiredOptions1);

        $requiredOptions2 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?query=bmx&format=xml&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent2 = 'test response 2';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent2, $requiredOptions2);
        $this->curlStub->setInformation($info, $requiredOptions2);

        $request1 = $this->factory->getRequest();

        $parameters = $request1->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $request1->setAction('TagCloud.ff');

        $request2 = $this->factory->getRequest();

        // This should not load the response for $request2, because it has not
        // yet been configured.
        $response1 = $request1->getResponse();
        $this->assertEquals(0, $response1->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response1->getConnectionError());
        $this->assertEquals(200, $response1->getHttpCode());
        $this->assertEquals($responseContent1, $response1->getContent());

        $this->assertInstanceOf('FACTFinder\Core\Server\NullResponse',
                                $request2->getResponse());

        // Now configure second connection
        $parameters = $request2->getParameters();

        $parameters['format'] = 'xml';
        $parameters['do'] = 'getTagCloud';

        $request2->setAction('TagCloud.ff');

        // Calling getResponse() for $request should not do anything, because
        // there is nothing to be done for $id1 itself.
        $this->assertSame($response1,  $request1->getResponse());

        // This should not load the second response and should NOT reload the
        // first one.
        $response = $request2->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
        $this->assertEquals('0', $response->getHttpCode());
        $this->assertEquals('', $response->getContent());

        $this->assertSame($response1, $request1->getResponse());
    }
}
