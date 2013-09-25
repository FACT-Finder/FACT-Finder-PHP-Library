<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class UrlBuilderTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var FACTFinder\Util\Parameters
     */
    protected $parameters;

    public function setUp()
    {
        parent::setUp();

        $this->urlBuilder = FF::getInstance(
            'Core\Server\UrlBuilder',
            self::$dic['loggerClass'],
            self::$dic['configuration']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = self::$dic['configuration'];

        $this->parameters = FF::getInstance('Util\Parameters');
    }

    public function testNonAuthenticationUrl()
    {
        $expectedAction = 'Test.ff';

        $this->parameters['format'] = 'json';

        $expectedPath = '/' . $this->configuration->getContext()
                      . '/' . $expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getNonAuthenticationUrl($expectedAction,
                                                       $this->parameters)
        );
    }

    public function testSimpleAuthenticationUrl()
    {
        $this->configuration->makeSimpleAuthenticationType();

        $expectedAction = 'Test.ff';

        $this->parameters['format'] = 'json';

        $expectedPath = '/' . $this->configuration->getContext()
                      . '/' . $expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json',
            'timestamp' => '%d',
            'username' => $this->configuration->getUserName(),
            'password' => md5($this->configuration->getPassword())
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getAuthenticationUrl($expectedAction,
                                                    $this->parameters)
        );
    }

    public function testAdvancedAuthenticationUrl()
    {
        $this->configuration->makeAdvancedAuthenticationType();

        $expectedAction = 'Test.ff';

        $this->parameters['format'] = 'json';

        $expectedPath = '/' . $this->configuration->getContext()
                      . '/' . $expectedAction;

        $url = $this->urlBuilder->getAuthenticationUrl($expectedAction,
                                                       $this->parameters);

        $tempParameters = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $tempParameters);
        $timestamp = $tempParameters['timestamp'];

        $pwHash = md5($this->configuration->getPassword());
        $prefix = $this->configuration->getAuthenticationPrefix();
        $postfix = $this->configuration->getAuthenticationPostfix();

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json',
            'timestamp' => $timestamp,
            'username' => $this->configuration->getUserName(),
            'password' => md5($prefix . time() . "000" . $pwHash . $postfix)
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getAuthenticationUrl($expectedAction,
                                                    $this->parameters)
        );
    }

    public function testHttpAuthenticationUrl()
    {
        $this->configuration->makeHttpAuthenticationType();

        $expectedAction = 'Test.ff';

        $this->parameters['format'] = 'json';

        $expectedPath = '/' . $this->configuration->getContext()
                      . '/' . $expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getAuthenticationUrl($expectedAction,
                                                    $this->parameters),
            $this->configuration->getUserName(),
            $this->configuration->getPassword()
        );
    }

    public function testOverwriteChannel()
    {
        $expectedAction = 'Test.ff';

        $this->parameters['format'] = 'json';
        $this->parameters['channel'] = 'en';

        $expectedPath = '/' . $this->configuration->getContext()
                      . '/' . $expectedAction;

        $expectedParameters = array(
            'channel' => 'en',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getNonAuthenticationUrl($expectedAction,
                                                       $this->parameters)
        );
    }

    private function assertUrlEquals(
        $expectedPath,
        $expectedParameters,
        $actualUrl,
        $expectedUser = null,
        $expectedPassword = null
    ) {
        $this->assertStringMatchesFormat($expectedPath, parse_url($actualUrl, PHP_URL_PATH));
        if(!is_null($expectedUser))
            $this->assertStringMatchesFormat($expectedUser, parse_url($actualUrl, PHP_URL_USER));
        if(!is_null($expectedPassword))
            $this->assertStringMatchesFormat($expectedPassword, parse_url($actualUrl, PHP_URL_PASS));

        $actualParameters = array();
        parse_str(parse_url($actualUrl, PHP_URL_QUERY), $actualParameters);

        $this->assertEquals(count($expectedParameters), count($actualParameters));

        foreach($expectedParameters as $key => $value)
        {
            $this->assertArrayHasKey($key, $actualParameters);
            $this->assertStringMatchesFormat($value, $actualParameters[$key]);
        }
    }
}
