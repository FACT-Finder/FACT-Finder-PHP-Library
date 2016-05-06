<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class FileSystemRequestFactoryTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\FileSystemRequestFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->factory = FF::getInstance(
            'Core\Server\FileSystemRequestFactory',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            FF::getInstance('Util\Parameters')
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];
    }

    public function testGetWorkingRequest()
    {
        $this->factory->setFileLocation(RESOURCES_DIR . DS . 'responses');
        $this->configuration->makeHttpAuthenticationType();

        $request = $this->factory->getRequest();

        $parameters = $request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $request->setAction('TagCloud.ff');

        $response = $request->getResponse();
        $expectedContent = file_get_contents(RESOURCES_DIR . DS
                                             . 'responses' . DS
                                             . 'TagCloud.86b6b33590e092674009abfe3d7fc170.json');
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($expectedContent, $response->getContent());
    }
}
