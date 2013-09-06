<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

class UrlBuilderTest extends BaseTestCase
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
     * @var FACTFinder\Core\UrlBuilder
     */
    protected $urlBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->urlBuilder = FF::getInstance(
            'Core\UrlBuilder',
            $this->dic['loggerClass'],
            $this->dic['configuration']
        );

        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->configuration = $this->dic['configuration'];
    }

    public function testSetAction()
    {
        $expectedAction = 'Test.ff';
        $this->urlBuilder->setAction($expectedAction);

        $this->assertEquals($expectedAction, $this->urlBuilder->getAction());
    }

    public function testNonAuthenticationUrl()
    {
        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->getParameters()->set('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getNonAuthenticationUrl()
        );
    }

    public function testSimpleAuthenticationUrl()
    {
        $this->configuration->setAuthenticationType('simple');

        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->getParameters()->set('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

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
            $this->urlBuilder->getAuthenticationUrl()
        );
    }

    public function testAdvancedAuthenticationUrl()
    {
        $this->configuration->setAuthenticationType('advanced');

        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->getParameters()->set('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $url = $this->urlBuilder->getAuthenticationUrl();

        $parameters = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $parameters);
        $timestamp = $parameters['timestamp'];

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
            $this->urlBuilder->getAuthenticationUrl()
        );
    }

    public function testHttpAuthenticationUrl()
    {
        $this->configuration->setAuthenticationType('http');

        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->getParameters()->set('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getAuthenticationUrl(),
            $this->configuration->getUserName(),
            $this->configuration->getPassword()
        );
    }

    public function testOverwriteChannel()
    {
        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $parameters = $this->urlBuilder->getParameters();
        $parameters['format'] = 'json';
        $parameters['channel'] = 'en';

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $expectedParameters = array(
            'channel' => 'en',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->urlBuilder->getNonAuthenticationUrl()
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
