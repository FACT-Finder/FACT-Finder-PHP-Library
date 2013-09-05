<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

class RequestParserTest extends BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\RequestParser the parser under test
     */
    private $requestParser;

    /**
     * @var mixed[] a backups of the superglobals $_SERVER, $_POST and $_GET
     */
    private $oldServer;
    private $oldPost;
    private $oldGet;

    public function setUp()
    {
        parent::setUp();

        $this->requestParser = FF::getInstance(
            'Core\RequestParser',
            $this->dic['loggerClass'],
            $this->dic['configuration'],
            $this->dic['encodingConverter']
        );

        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->oldServer = $_SERVER;
        $this->oldPost = $_POST;
        $this->oldGet = $_GET;
    }

    public function tearDown()
    {
        $_SERVER = $this->oldServer;
        $_POST = $this->oldPost;
        $_GET = $this->oldGet;
    }

    private function assertParameters($expectedParameters)
    {
        $this->assertEquals($expectedParameters, $this->requestParser
                                                      ->getRequestParameters()
                                                      ->getArray());
    }

    public function testRequestParametersFromQueryString()
    {
        $_SERVER['QUERY_STRING'] = 'a+b=c&d=e+f';

        $this->assertParameters(array(
            'a b' => 'c',
            'd' => 'e f',
        ));
    }

    public function testParametersWithMultipleValues()
    {
        $_SERVER['QUERY_STRING'] = 'a=1&a=2&b[]=3&b[]=4&b[]=5';

        $this->assertParameters(array(
            'a' => '2',
            'b' => array('3', '4', '5'),
        ));
    }

    public function testEmptyParameterNames()
    {
        $_SERVER['QUERY_STRING'] = '=1&=2&[]=3&[]=4';

        $this->assertParameters(array());
    }

    public function testRequestTarget()
    {
        $_SERVER['REQUEST_URI'] = '/index.php?foo=bar';

        $this->assertEquals('/index.php', $this->requestParser
                                               ->getRequestTarget());
    }
}
