<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class FileSystemDataProviderTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\FileSystemDataProvider
     */
    protected $dataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->dataProvider = FF::getInstance(
            'Core\Server\FileSystemDataProvider',
            self::$dic['loggerClass'],
            self::$dic['configuration']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];
    }

    public function testLoadResponse()
    {
        $this->dataProvider->setFileLocation(RESOURCES_DIR . DS . 'responses');
        $this->configuration->makeHttpAuthenticationType();

        $connectionData = FF::getInstance('Core\Server\ConnectionData');
        $id = $this->dataProvider->register($connectionData);

        $parameters = $connectionData->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $connectionData->setAction('TagCloud.ff');

        $this->dataProvider->loadResponse($id);

        $response = $connectionData->getResponse();
        $expectedContent = file_get_contents(RESOURCES_DIR . DS
                                             . 'responses' . DS
                                             . 'TagCloud.86b6b33590e092674009abfe3d7fc170.json');
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($expectedContent, $response->getContent());
    }
}
