<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class RequestTest extends \FACTFinder\Test\BaseTestCase
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

    public function setUp()
    {
        parent::setUp();

        $dataProvider = FF::getInstance(
            'Core\Server\FileSystemDataProvider',
            self::$dic['loggerClass'],
            self::$dic['configuration']
        );

        $dataProvider->setFileLocation(RESOURCES_DIR . DS . 'responses');

        $this->request = FF::getInstance(
            'Core\Server\Request',
            self::$dic['loggerClass'],
            FF::getInstance('Core\Server\ConnectionData'),
            $dataProvider
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];
    }

    public function testGetResponse()
    {
        $this->configuration->makeHttpAuthenticationType();

        $parameters = $this->request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $this->request->setAction('TagCloud.ff');

        $response = $this->request->getResponse();
        $expectedContent = file_get_contents(RESOURCES_DIR . DS
                                             . 'responses' . DS
                                             . 'TagCloud.86b6b33590e092674009abfe3d7fc170.json');
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($expectedContent, $response->getContent());
    }
    
    public function testResetLoaded()
    {
        //setup first request
        $this->configuration->makeHttpAuthenticationType();

        $parameters = $this->request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $this->request->setAction('TagCloud.ff');

        $response = $this->request->getResponse();
        $expectedContent = file_get_contents(RESOURCES_DIR . DS
                                             . 'responses' . DS
                                             . 'TagCloud.86b6b33590e092674009abfe3d7fc170.json');
        $this->assertEquals($expectedContent, $response->getContent());
        
        //setup second request without changing parameters
        $this->request->resetLoaded();
        $response2 = $this->request->getResponse();
        //should not be reloaded as url/parameters did not change
        $this->assertSame($response, $response2);
        
        //setup third request with changed parameters
        $this->request->resetLoaded();
        $parameters['wordCount'] = '3';
        $response2 = $this->request->getResponse();
        //should be reloaded as url/parameters did change
        $this->assertNotSame($response, $response2);
    }
}
