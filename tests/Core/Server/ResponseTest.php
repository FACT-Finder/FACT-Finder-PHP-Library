<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class ResponseTest extends \FACTFinder\Test\BaseTestCase
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

    public function testResponse()
    {
        $response = FF::getInstance(
            'Core\Server\Response',
            'response content',
            200,
            CURLE_OK,
            'CURLE_OK'
        );

        $this->assertEquals('response content', $response->getContent());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals(CURLE_OK, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
    }
}
