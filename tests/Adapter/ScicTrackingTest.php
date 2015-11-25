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
        $_SERVER['QUERY_STRING'] = 'event=cart&id=1&price=4&count=3&sid=mysid&userId=5';

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
            'mysid',
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
            'mysid',
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
            'mysid',
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
            'mysid',
            3
        );

        $this->assertTrue($result);
    }
}
