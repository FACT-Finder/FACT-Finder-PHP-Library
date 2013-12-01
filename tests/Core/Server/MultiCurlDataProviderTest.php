<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class MultiCurlDataProviderTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\MultiCurlDataProvider
     */
    protected $dataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->curlStub = FF::getInstance('Util\CurlStub');
        $this->dataProvider = FF::getInstance(
            'Core\Server\MultiCurlDataProvider',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            $this->curlStub,
            self::$dic['serverUrlBuilder']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];
    }

    public function testLoadResponse()
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


    public function testLoadInParallel()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions1 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=json&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent1 = 'test response 1';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent1, $requiredOptions1);
        $this->curlStub->setInformation($info, $requiredOptions1);

        $requiredOptions2 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=xml&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent2 = 'test response 2';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent2, $requiredOptions2);
        $this->curlStub->setInformation($info, $requiredOptions2);

        $connectionData1 = FF::getInstance('Core\Server\ConnectionData');
        $id1 = $this->dataProvider->register($connectionData1);

        $parameters = $connectionData1->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $connectionData1->setAction('TagCloud.ff');

        $connectionData2 = FF::getInstance('Core\Server\ConnectionData');
        $id2 = $this->dataProvider->register($connectionData2);

        $parameters = $connectionData2->getParameters();

        $parameters['format'] = 'xml';
        $parameters['do'] = 'getTagCloud';

        $connectionData2->setAction('TagCloud.ff');

        // This should load the content for $id2 as well
        $this->dataProvider->loadResponse($id1);

        $response = $connectionData1->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent1, $response->getContent());

        $response = $connectionData2->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent2, $response->getContent());
    }

    // This is NOT how the MultiCurlDataProvider should be used in production.
    // In fact, it will log a warning whenever it is used like this. If
    // possible, always configure all connections first and then use
    // loadResponse().
    public function testLoadInSequence()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions1 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=json&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent1 = 'test response 1';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent1, $requiredOptions1);
        $this->curlStub->setInformation($info, $requiredOptions1);

        $requiredOptions2 = array(
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=xml&do=getTagCloud&verbose=true&channel=de'
        );
        $responseContent2 = 'test response 2';
        $info = array(
            CURLINFO_HTTP_CODE => '200'
        );

        $this->curlStub->setResponse($responseContent2, $requiredOptions2);
        $this->curlStub->setInformation($info, $requiredOptions2);

        $connectionData1 = FF::getInstance('Core\Server\ConnectionData');
        $id1 = $this->dataProvider->register($connectionData1);

        $parameters = $connectionData1->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $connectionData1->setAction('TagCloud.ff');

        $connectionData2 = FF::getInstance('Core\Server\ConnectionData');
        $id2 = $this->dataProvider->register($connectionData2);

        // This should not load the content for $id2, because it has not yet
        // been configured.
        $this->dataProvider->loadResponse($id1);

        $response1 = $connectionData1->getResponse();
        $this->assertEquals(0, $response1->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response1->getConnectionError());
        $this->assertEquals(200, $response1->getHttpCode());
        $this->assertEquals($responseContent1, $response1->getContent());

        $this->assertInstanceOf('FACTFinder\Core\Server\NullResponse',
                                $connectionData2->getResponse());

        // Now configure second connection
        $parameters = $connectionData2->getParameters();

        $parameters['format'] = 'xml';
        $parameters['do'] = 'getTagCloud';

        $connectionData2->setAction('TagCloud.ff');

        // Calling loadResponse() for $id1 should not load the second
        // connection, because there is nothing to be done for $id1 itself.
        $this->dataProvider->loadResponse($id1);

        $this->assertSame($response1, $connectionData1->getResponse());
        $this->assertInstanceOf('FACTFinder\Core\Server\NullResponse',
                                $connectionData2->getResponse());

        // This should now load the second response but should NOT reload the
        // first one.
        $this->dataProvider->loadResponse($id2);

        $this->assertSame($response1, $connectionData1->getResponse());

        $response2 = $connectionData2->getResponse();
        $this->assertEquals(0, $response2->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response2->getConnectionError());
        $this->assertEquals(200, $response2->getHttpCode());
        $this->assertEquals($responseContent2, $response2->getContent());
    }
}
