<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;
use FACTFinder\Core\ManualConfiguration;

class ManualConfigurationTest extends BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    public function setUp()
    {
        parent::setUp();
        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testValuesSetInConstructor()
    {
        $configuration = FF::getInstance(
            'Core\ManualConfiguration',
            array(
                'port' => 80,
                'channel' => 'de',
                'authenticationType' => ManualConfiguration::HTTP_AUTHENTICATION,
                'ignoredClientParameters' => array(
                    'channel' => true
                ),
                'test' => 'value'
            )
        );

        $this->assertEquals(80, $configuration->getServerPort());
        $this->assertEquals("de", $configuration->getChannel());

        $this->assertTrue($configuration->isHttpAuthenticationType());

        $expectedIgnoredClientParameters = array(
            'channel' => true
        );

        $this->assertEquals($expectedIgnoredClientParameters, $configuration->getIgnoredClientParameters());

        $this->assertEquals('value', $configuration->getCustomValue('test'));
    }

    public function testValuesSetManually()
    {
        $configuration = FF::getInstance(
            'Core\ManualConfiguration',
            array()
        );

        $configuration->port = 80;
        $configuration->channel = 'de';
        $configuration->authenticationType = ManualConfiguration::HTTP_AUTHENTICATION;
        $configuration->ignoredClientParameters = array(
            'channel' => true
        );
        $configuration->test = 'value';

        $this->assertEquals(80, $configuration->getServerPort());
        $this->assertEquals("de", $configuration->getChannel());

        $this->assertTrue($configuration->isHttpAuthenticationType());

        $expectedIgnoredClientParameters = array(
            'channel' => true
        );

        $this->assertEquals($expectedIgnoredClientParameters, $configuration->getIgnoredClientParameters());

        $this->assertEquals('value', $configuration->getCustomValue('test'));
    }

    /**
     * Accessing an unset value should internally raise a PHP error.
     * @expectedException PHPUnit_Framework_Error
     */
    public function testUnavailableValue()
    {
        $configuration = FF::getInstance(
            'Core\ManualConfiguration',
            array('port' => 80)
        );

        $configuration->channel = 'de';

        $configuration->getRequestProtocol();
    }
}
