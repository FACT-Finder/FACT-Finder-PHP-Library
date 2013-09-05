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

    public function testSetSingleParameter()
    {
        $this->urlBuilder->setParameter('query', 'bmx');

        $actualParameters = $this->urlBuilder->getParameters();

        $this->assertCount(1, $actualParameters);
        $this->assertArrayHasKey('query', $actualParameters);
        $this->assertEquals('bmx', $actualParameters['query']);

        $this->urlBuilder->setParameter('format', 'json');

        $actualParameters = $this->urlBuilder->getParameters();

        $this->assertCount(2, $actualParameters);
        $this->assertArrayHasKey('format', $actualParameters);
        $this->assertEquals('json', $actualParameters['format']);
    }

    public function testSetParameters()
    {
        $this->urlBuilder->setParameter('query', 'bmx');
        $this->urlBuilder->setParameter('format', 'json');

        $this->urlBuilder->setParameters(array(
            'verbose' => 'true',
            'format' => 'xml',
        ));

        $expectedParameters = array(
            'query' => 'bmx',
            'format' => 'xml',
            'verbose' => 'true',
        );

        $actualParameters = $this->urlBuilder->getParameters();

        $this->assertEquals($expectedParameters, $actualParameters);
    }

    public function testResetParameters()
    {
        $this->urlBuilder->setParameter('query', 'bmx');
        $this->urlBuilder->setParameter('format', 'json');

        $expectedParameters = array(
            'query' => 'bmx',
            'channel' => 'de',
            'verbose' => 'true'
        );

        $this->urlBuilder->resetParameters($expectedParameters);

        $actualParameters = $this->urlBuilder->getParameters();

        $this->assertArrayNotHasKey('format', $actualParameters);
        $this->assertEquals($expectedParameters, $actualParameters);
    }

    public function testUnsetParameter()
    {
        $this->urlBuilder->setParameter('query', 'bmx');
        $this->urlBuilder->setParameter('format', 'json');

        $this->urlBuilder->unsetParameter('format');

        $actualParameters = $this->urlBuilder->getParameters();

        $this->assertCount(1, $actualParameters);
        $this->assertArrayHasKey('query', $actualParameters);
        $this->assertArrayNotHasKey('format', $actualParameters);
    }

    public function testArrayParameter()
    {
        $this->urlBuilder->setParameter('productIds', '123');
        $this->urlBuilder->addParameter('productIds', '456');

        $actualParameters = $this->urlBuilder->getParameters();

        $this->assertCount(1, $actualParameters);
        $this->assertArrayHasKey('productIds', $actualParameters);
        $this->assertEquals(array('123', '456'), $actualParameters['productIds']);

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
        $this->urlBuilder->setParameter('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            null,
            null,
            $this->urlBuilder->getNonAuthenticationUrl()
        );
    }

    public function testSimpleAuthenticationUrl()
    {
        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->setParameter('format', 'json');

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
            null,
            null,
            $this->urlBuilder->getSimpleAuthenticationUrl()
        );
    }

    public function testAdvancedAuthenticationUrl()
    {
        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->setParameter('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $url = $this->urlBuilder->getAdvancedAuthenticationUrl();

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
            null,
            null,
            $this->urlBuilder->getAdvancedAuthenticationUrl()
        );
    }

    public function testHttpAuthenticationUrl()
    {
        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->setParameter('format', 'json');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $expectedParameters = array(
            'channel' => 'de',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            $this->configuration->getUserName(),
            $this->configuration->getPassword(),
            $this->urlBuilder->getHttpAuthenticationUrl()
        );
    }

    public function testOverwriteChannel()
    {
        $expectedAction = 'Test.ff';

        $this->urlBuilder->setAction($expectedAction);
        $this->urlBuilder->setParameter('format', 'json');
        $this->urlBuilder->setParameter('channel', 'en');

        $expectedPath = '/'.$this->configuration->getContext().'/'.$expectedAction;

        $expectedParameters = array(
            'channel' => 'en',
            'format' => 'json'
        );

        $this->assertUrlEquals(
            $expectedPath,
            $expectedParameters,
            null,
            null,
            $this->urlBuilder->getNonAuthenticationUrl()
        );
    }

    private function assertUrlEquals($expectedPath, $expectedParameters, $expectedUser = null, $expectedPassword = null, $actualUrl)
    {
        $this->assertStringMatchesFormat($expectedPath, parse_url($actualUrl, PHP_URL_PATH));
        if($expectedUser !== null)
            $this->assertStringMatchesFormat($expectedUser, parse_url($actualUrl, PHP_URL_USER));
        if($expectedPassword !== null)
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
