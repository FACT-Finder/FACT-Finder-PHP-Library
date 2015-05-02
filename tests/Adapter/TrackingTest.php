<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class TrackingTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\Tracking
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        // For the request parser to retrieve
        $_SERVER['QUERY_STRING'] = 'event=cart&id=1&masterId=2&title=product&sid=eccbc87e4b5ce2fe28308fd9f2a7baf3&cookieId=a87ff679a2f3e71d9181a67b7542122c&count=5&price=6&userId=7&query=query';

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\Tracking',
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
            'query',
            3,
            4,
            md5(5),
            md5(6),
            7,
            8,
            100,
            'product',
            9,
            10,
            11
        );
        $this->assertTrue($result);
    }
    
    public function testTrackCart()
    {
        $result = $this->adapter->trackCart(
            1,
            2,
            'product',
            'query',
            md5(3),
            md5(4),
            5,
            6.00,
            7
        );

        $this->assertTrue($result);
    }
    
    public function testTrackCheckout()
    {
        $result = $this->adapter->trackCheckout(
            1,
            2,
            'product',
            'query',
            md5(3),
            md5(4),
            5,
            6.00,
            7
        );

        $this->assertTrue($result);
    }

    public function testTrackRecommendationClick()
    {
        $result = $this->adapter->trackRecommendationClick(
            1,
            2,
            3,  
            md5(4),
            md5(5),
            6
        );

        $this->assertTrue($result);
    }
    
    public function testTrackLogin()
    {
        $result = $this->adapter->trackLogin(
            md5(1),
            md5(2),
            3
        );

        $this->assertTrue($result);
    }
}
