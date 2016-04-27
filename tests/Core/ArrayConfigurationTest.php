<?php
namespace FACTFinder\Test\Core;

use FACTFinder\Loader as FF;

class ArrayConfigurationTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var \FACTFinder\Core\ArrayConfiguration the configuration under test
     */
    private $configuration;

    public function setUp()
    {
        parent::setUp();

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];
    }

    public function testTopLevelSettings()
    {
        $this->assertTrue($this->configuration->isDebugEnabled());
        $this->assertEquals('php-value', $this->configuration->getCustomValue('custom'));
    }

    public function testConnectionSettings()
    {
        $this->assertEquals('http', $this->configuration->getRequestProtocol());
        $this->assertEquals('demoshop.fact-finder.de', $this->configuration->getServerAddress());
        $this->assertEquals(80, $this->configuration->getServerPort());
        $this->assertEquals('FACT-Finder', $this->configuration->getContext());
        $this->assertEquals('de', $this->configuration->getChannel());
        $this->assertEquals('de', $this->configuration->getLanguage());

        $this->assertTrue($this->configuration->isAdvancedAuthenticationType());
        $this->assertFalse($this->configuration->isSimpleAuthenticationType());
        $this->assertFalse($this->configuration->isHttpAuthenticationType());
        $this->assertEquals('user', $this->configuration->getUserName());
        $this->assertEquals('userpw', $this->configuration->getPassword());
        $this->assertEquals('FACT-FINDER', $this->configuration->getAuthenticationPrefix());
        $this->assertEquals('FACT-FINDER', $this->configuration->getAuthenticationPostfix());

        $this->assertEquals(2,   $this->configuration->getDefaultConnectTimeout());
        $this->assertEquals(4,   $this->configuration->getDefaultTimeout());
        $this->assertEquals(1,   $this->configuration->getSuggestConnectTimeout());
        $this->assertEquals(2,   $this->configuration->getSuggestTimeout());
        $this->assertEquals(1,   $this->configuration->getTrackingConnectTimeout());
        $this->assertEquals(2,   $this->configuration->getTrackingTimeout());
        $this->assertEquals(10,  $this->configuration->getImportConnectTimeout());
        $this->assertEquals(360, $this->configuration->getImportTimeout());
    }

    public function testParameterSettings()
    {
        $expectedIgnoredServerParameters = array(
            'password' => true,
            'username' => true,
            'timestamp' => true
        );

        $this->assertEquals($expectedIgnoredServerParameters, $this->configuration->getIgnoredServerParameters());

        $expectedIgnoredClientParameters = array(
            'xml' => true,
            'format' => true,
            'channel' => true,
            'password' => true,
            'username' => true,
            'timestamp' => true
        );

        $this->assertEquals($expectedIgnoredClientParameters, $this->configuration->getIgnoredClientParameters());

        $expectedRequiredServerParameters = array();

        $this->assertEquals($expectedRequiredServerParameters, $this->configuration->getRequiredServerParameters());

        $expectedRequiredClientParameters = array();

        $this->assertEquals($expectedRequiredClientParameters, $this->configuration->getRequiredClientParameters());

        $expectedServerMappings = array(
            'keywords' => 'query'
        );

        $this->assertEquals($expectedServerMappings, $this->configuration->getServerMappings());

        $expectedClientMappings = array(
            'query' => 'keywords'
        );

        $this->assertEquals($expectedClientMappings, $this->configuration->getClientMappings());

        $expectedServerWhitelist = array(
            'query' => true,
            '/^filter.*/' => true,
            'followSearch' => true
        );
        
        $this->assertArraySubset($expectedServerWhitelist, $this->configuration->getWhitelistServerParameters());
        
        $expectedClientWhitelist = array(
            'keywords' => true,
            '/^filter.*/' => true,
            'followSearch' => true
        );
        
        $this->assertArraySubset($expectedClientWhitelist, $this->configuration->getWhitelistClientParameters());
    }

    public function testEncodingSettings()
    {
        $this->assertEquals('UTF-8', $this->configuration->getPageContentEncoding());
        $this->assertEquals('ISO-8859-1', $this->configuration->getClientUrlEncoding());
    }

    public function testSetAuthenticationType()
    {
        $this->assertTrue($this->configuration->isAdvancedAuthenticationType());
        $this->assertFalse($this->configuration->isSimpleAuthenticationType());
        $this->assertFalse($this->configuration->isHttpAuthenticationType());

        $this->configuration->makeSimpleAuthenticationType();
        $this->assertFalse($this->configuration->isAdvancedAuthenticationType());
        $this->assertTrue($this->configuration->isSimpleAuthenticationType());
        $this->assertFalse($this->configuration->isHttpAuthenticationType());

        $this->configuration->makeHttpAuthenticationType();
        $this->assertFalse($this->configuration->isAdvancedAuthenticationType());
        $this->assertFalse($this->configuration->isSimpleAuthenticationType());
        $this->assertTrue($this->configuration->isHttpAuthenticationType());

        $this->configuration->makeAdvancedAuthenticationType();
        $this->assertTrue($this->configuration->isAdvancedAuthenticationType());
        $this->assertFalse($this->configuration->isSimpleAuthenticationType());
        $this->assertFalse($this->configuration->isHttpAuthenticationType());
    }

    public function testSetEncodings()
    {
        $this->configuration->setPageContentEncoding('ISO-8859-1');
        $this->configuration->setClientUrlEncoding('ISO-8859-15');

        $this->assertEquals('ISO-8859-1', $this->configuration->getPageContentEncoding());
        $this->assertEquals('ISO-8859-15', $this->configuration->getClientUrlEncoding());
    }
}
