<?php
namespace FACTFinder\Test\Core\Server;

use FACTFinder\Loader as FF;

class NullResponseTest extends \FACTFinder\Test\BaseTestCase
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
        $response = FF::getInstance('Core\Server\NullResponse');

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(0, $response->getHttpCode());
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
    }
}
