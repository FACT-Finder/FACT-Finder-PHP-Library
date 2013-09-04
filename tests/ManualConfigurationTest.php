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
        $config = FF::getInstance(
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

        $this->assertEquals(80, $config->getServerPort());
        $this->assertEquals("de", $config->getChannel());

        $this->assertTrue($config->isHttpAuthenticationType());

        $expectedIgnoredClientParameters = array(
            'channel' => true
        );

        $this->assertEquals($expectedIgnoredClientParameters, $config->getIgnoredClientParameters());

        $this->assertEquals('value', $config->getCustomValue('test'));
    }

    public function testValuesSetManually()
    {
        $config = FF::getInstance(
            'Core\ManualConfiguration',
            array()
        );

        $config->port = 80;
        $config->channel = 'de';
        $config->authenticationType = ManualConfiguration::HTTP_AUTHENTICATION;
        $config->ignoredClientParameters = array(
            'channel' => true
        );
        $config->test = 'value';

        $this->assertEquals(80, $config->getServerPort());
        $this->assertEquals("de", $config->getChannel());

        $this->assertTrue($config->isHttpAuthenticationType());

        $expectedIgnoredClientParameters = array(
            'channel' => true
        );

        $this->assertEquals($expectedIgnoredClientParameters, $config->getIgnoredClientParameters());

        $this->assertEquals('value', $config->getCustomValue('test'));
    }

    /**
     * Accessing an unset value should internally raise a PHP error.
     * @expectedException PHPUnit_Framework_Error
     */
    public function testUnavailableValue()
    {
        $config = FF::getInstance(
            'Core\ManualConfiguration',
            array('port' => 80)
        );

        $config->channel = 'de';

        $config->getRequestProtocol();
    }
}
