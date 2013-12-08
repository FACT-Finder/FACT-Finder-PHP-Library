<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class ScicTrackingTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\ScicTracking
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        // For the request parser to retrieve
        $_SERVER['QUERY_STRING'] = 'event=cart&id=1&price=4&count=3&sid=c81e728d9d4c2f636f067f89cc14862c&userid=5';

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\ScicTracking',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testTrackingFromRequest()
    {
        $this->assertTrue($this->adapter->doTrackingFromRequest());
    }

    public function testTrackClick()
    {
        $result = $this->adapter->trackClick(
            1,
            md5(2),
            'query',
            3,
            4,
            5,
            100,
            'product',
            9,
            15
        );

        $this->assertTrue($result);
    }

    public function testTrackCart()
    {
        $result = $this->adapter->trackCart(
            1,
            md5(2),
            3,
            4.00,
            5
        );

        $this->assertTrue($result);
    }

    public function testTrackCheckout()
    {
        $result = $this->adapter->trackCheckout(
            1,
            md5(2),
            3,
            4.00,
            5
        );

        $this->assertTrue($result);
    }

    public function testTrackRecommendationClick()
    {
        $result = $this->adapter->trackRecommendationClick(
            1,
            md5(2),
            3
        );

        $this->assertTrue($result);
    }
}
