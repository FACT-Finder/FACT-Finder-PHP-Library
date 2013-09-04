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

    public function setUp()
    {
        parent::setUp();

        /*$this->requestParser = FF::getInstance(
            'Core\RequestParser',
            $this->dic['loggerClass'],
            $this->dic['configuration']
        );

        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);*/
    }

    public function testGetRequestParameters()
    {
    }
}
