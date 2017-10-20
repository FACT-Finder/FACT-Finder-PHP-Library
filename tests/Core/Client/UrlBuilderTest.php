<?php
namespace FACTFinder\Test\Core\Client;

use FACTFinder\Loader as FF;

class UrlBuilderTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\Client\UrlBuilder
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
            'Core\Client\UrlBuilder',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['requestParser'],
            self::$dic['encodingConverter']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->parameters = FF::getInstance('Util\Parameters');
    }

    public function testGenerateUrlFromRequestTarget()
    {
        $_SERVER['REQUEST_URI'] = 'has%20spaces/index.php';

        $this->parameters['format'] = 'json'; // is ignored
        $this->parameters['foo'] = "b\xC3\xA4r"; // UTF-8 encoded 'bär'
        $this->parameters['query'] = 'bmx bike'; // maps to 'keywords' and has spaces


        $actualUrl = $this->urlBuilder->generateUrl($this->parameters);

        // The 'ä' gets ISO-8859-1 encoded.
        // This test is a bit too brittle, as it depends no the order of the
        // query parameters (although it shouldn't).
        $this->assertEquals(
            'has spaces/index.php?foo=b%E4r&keywords=bmx%20bike',
            $actualUrl
        );
    }

    public function testGenerateUrlWithSeoPathFromRequestTarget()
    {
        $_SERVER['REQUEST_URI'] = 'has%20spaces/index.php';

        $this->parameters['format'] = 'json'; // is ignored
        $this->parameters['seoPath'] = '/a b'; // less common seo path which has spaces

        $actualUrl = $this->urlBuilder->generateUrl($this->parameters);

        $this->assertEquals(
            'has spaces/index.php/s/a b?',
            $actualUrl
        );
    }

    public function testGenerateUrlFromExplicitTarget()
    {
        $_SERVER['REQUEST_URI'] = '/index.php';

        $this->parameters['format'] = 'json'; // is ignored
        $this->parameters['foo'] = "b\xC3\xA4r"; // UTF-8 encoded 'bär'
        $this->parameters['query'] = 'bmx bike'; // maps to 'keywords' and has spaces

        $actualUrl = $this->urlBuilder->generateUrl(
            $this->parameters,
            '/detail.php'
        );

        // The 'ä' gets ISO-8859-1 encoded.
        // This test is a bit too brittle, as it depends no the order of the
        // query parameters (although it shouldn't).
        $this->assertEquals(
            '/detail.php?foo=b%E4r&keywords=bmx%20bike',
            $actualUrl
        );
    }

    public function testGenerateUrlWithSeoPathFromExplicitTarget()
    {
        $_SERVER['REQUEST_URI'] = 'has%20spaces/index.php';

        $this->parameters['format'] = 'json'; // is ignored
        $this->parameters['seoPath'] = '/a-b'; // common seo path with no spaces

        $actualUrl = $this->urlBuilder->generateUrl(
            $this->parameters,
            '/'
        );

        $this->assertEquals(
            '/s/a-b?',
            $actualUrl
        );
    }
}
