<?php
namespace FACTFinder\Test\Core;

use FACTFinder\Loader as FF;
use FACTFinder\Core\ManualConfiguration;

class ManualConfigurationTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    public function setUp()
    {
        parent::setUp();
        $loggerClass = self::$dic['loggerClass'];
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

        $configuration->debug = true;
        $configuration->requestProtocol = 'http';
        $configuration->serverAddress = 'demoshop.fact-finder.de';
        $configuration->port = 80;
        $configuration->context = 'FACT-Finder';
        $configuration->channel = 'de';
        $configuration->language = 'de';

        $configuration->authenticationType = ManualConfiguration::ADVANCED_AUTHENTICATION;
        $configuration->userName = 'user';
        $configuration->password = 'userpw';
        $configuration->authenticationPrefix = 'FACT-FINDER';
        $configuration->authenticationPostfix = 'FACT-FINDER';

        $configuration->defaultConnectTimeout = 2;
        $configuration->defaultTimeout = 4;
        $configuration->suggestConnectTimeout = 1;
        $configuration->suggestTimeout = 2;
        $configuration->scicConnectTimeout = 1;
        $configuration->scicTimeout = 2;
        $configuration->importConnectTimeout = 10;
        $configuration->importTimeout = 360;

        $expectedIgnoredServerParameters = array(
            'sid' => true,
            'password' => true,
            'username' => true,
            'timestamp' => true
        );
        $configuration->ignoredServerParameters = $expectedIgnoredServerParameters;

        $expectedIgnoredClientParameters = array(
            'xml' => true,
            'format' => true,
            'channel' => true,
            'password' => true,
            'username' => true,
            'timestamp' => true
        );
        $configuration->ignoredClientParameters = $expectedIgnoredClientParameters;

        $expectedRequiredServerParameters = array();
        $configuration->requiredServerParameters = $expectedRequiredServerParameters;

        $expectedRequiredClientParameters = array(
            'test' => 'value'
        );
        $configuration->requiredClientParameters = $expectedRequiredClientParameters;

        $expectedServerMappings = array(
            'keywords' => 'query'
        );
        $configuration->serverMappings = $expectedServerMappings;

        $expectedClientMappings = array(
            'query' => 'keywords'
        );
        $configuration->clientMappings = $expectedClientMappings;

        $configuration->pageContentEncoding = 'UTF-8';
        $configuration->clientUrlEncoding = 'UTF-8';

        $configuration->test = 'value';

        $this->assertTrue($configuration->isDebugEnabled());
        $this->assertEquals('value', $configuration->getCustomValue('test'));

        $this->assertEquals('http', $configuration->getRequestProtocol());
        $this->assertEquals('demoshop.fact-finder.de', $configuration->getServerAddress());
        $this->assertEquals(80, $configuration->getServerPort());
        $this->assertEquals('FACT-Finder', $configuration->getContext());
        $this->assertEquals('de', $configuration->getChannel());
        $this->assertEquals('de', $configuration->getLanguage());

        $this->assertTrue($configuration->isAdvancedAuthenticationType());
        $this->assertFalse($configuration->isSimpleAuthenticationType());
        $this->assertFalse($configuration->isHttpAuthenticationType());
        $this->assertEquals('user', $configuration->getUserName());
        $this->assertEquals('userpw', $configuration->getPassword());
        $this->assertEquals('FACT-FINDER', $configuration->getAuthenticationPrefix());
        $this->assertEquals('FACT-FINDER', $configuration->getAuthenticationPostfix());

        $this->assertEquals(2,   $configuration->getDefaultConnectTimeout());
        $this->assertEquals(4,   $configuration->getDefaultTimeout());
        $this->assertEquals(1,   $configuration->getSuggestConnectTimeout());
        $this->assertEquals(2,   $configuration->getSuggestTimeout());
        $this->assertEquals(1,   $configuration->getScicConnectTimeout());
        $this->assertEquals(2,   $configuration->getScicTimeout());
        $this->assertEquals(10,  $configuration->getImportConnectTimeout());
        $this->assertEquals(360, $configuration->getImportTimeout());

        $this->assertEquals($expectedIgnoredServerParameters, $configuration->getIgnoredServerParameters());
        $this->assertEquals($expectedIgnoredClientParameters, $configuration->getIgnoredClientParameters());
        $this->assertEquals($expectedRequiredServerParameters, $configuration->getRequiredServerParameters());
        $this->assertEquals($expectedRequiredClientParameters, $configuration->getRequiredClientParameters());
        $this->assertEquals($expectedServerMappings, $configuration->getServerMappings());
        $this->assertEquals($expectedClientMappings, $configuration->getClientMappings());
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
