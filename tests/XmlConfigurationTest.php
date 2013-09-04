<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

class XmlConfigurationTest extends BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\XmlConfiguration the configuration under test
     */
    private $config;

    public function setUp()
    {
        parent::setUp();
        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->config = $this->dic['configuration'];
    }

    public function testConnectionSettings()
    {
        $this->assertEquals("http", $this->config->getRequestProtocol());
        $this->assertEquals("demoshop.fact-finder.de", $this->config->getServerAddress());
        $this->assertEquals(80, $this->config->getServerPort());
        $this->assertEquals("FACT-Finder6.7", $this->config->getContext());
        $this->assertEquals("de", $this->config->getChannel());
        $this->assertEquals("de", $this->config->getLanguage());

        $this->assertTrue($this->config->isAdvancedAuthenticationType());
        $this->assertEquals("user", $this->config->getUserName());
        $this->assertEquals("userpw", $this->config->getPassword());
        $this->assertEquals("FACT-FINDER", $this->config->getAuthenticationPrefix());
        $this->assertEquals("FACT-FINDER", $this->config->getAuthenticationPostfix());

        $this->assertEquals(2,   $this->config->getDefaultConnectTimeout());
        $this->assertEquals(4,   $this->config->getDefaultTimeout());
        $this->assertEquals(1,   $this->config->getSuggestConnectTimeout());
        $this->assertEquals(2,   $this->config->getSuggestTimeout());
        $this->assertEquals(1,   $this->config->getScicConnectTimeout());
        $this->assertEquals(2,   $this->config->getScicTimeout());
        $this->assertEquals(10,  $this->config->getImportConnectTimeout());
        $this->assertEquals(360, $this->config->getImportTimeout());
    }

    public function testParameterSettings()
    {
        $expectedIgnoredServerParameters = array(
            "sid" => true,
            "password" => true,
            "username" => true,
            "timestamp" => true
        );

        $this->assertEquals($expectedIgnoredServerParameters, $this->config->getIgnoredServerParameters());

        $expectedIgnoredClientParameters = array(
            "xml" => true,
            "format" => true,
            "channel" => true,
            "password" => true,
            "username" => true,
            "timestamp" => true
        );

        $this->assertEquals($expectedIgnoredClientParameters, $this->config->getIgnoredClientParameters());

        $expectedRequiredServerParameters = array();

        $this->assertEquals($expectedRequiredServerParameters, $this->config->getRequiredServerParameters());

        $expectedRequiredClientParameters = array(
            "test" => "value"
        );

        $this->assertEquals($expectedRequiredClientParameters, $this->config->getRequiredClientParameters());

        $expectedServerMappings = array(
            "keywords" => "query"
        );

        $this->assertEquals($expectedServerMappings, $this->config->getServerMappings());

        $expectedClientMappings = array(
            "query" => "keywords"
        );

        $this->assertEquals($expectedClientMappings, $this->config->getClientMappings());
    }

    public function testEncodingSettings()
    {
        $this->assertEquals('UTF-8', $this->config->getPageContentEncoding());
        $this->assertEquals('UTF-8', $this->config->getClientUrlEncoding());
    }
}
