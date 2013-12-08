<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class AnalyticDashboardTrackingTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\AnalyticDashboardTracking
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        // For the request parser to retrieve
        $_SERVER['QUERY_STRING'] = 'event=sessionStart&sid=123&uid=456';

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\AnalyticDashboardTracking',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testTrackingFromRequest()
    {
        $this->assertTrue($this->adapter->applyTracking());
    }

    /**
     * Some events require the sourceRefKey parameter to be set.
     * @expectedException InvalidArgumentException
     */
    public function testMissingSourceRefKey()
    {
        $trackingEventTypeEnum = FF::getClassName('Data\TrackingEventType');
        $this->adapter->setEventType($trackingEventTypeEnum::Display());
        $this->adapter->applyTracking();
    }

    public function testOverrideRequestParameters()
    {
        $trackingEventTypeEnum = FF::getClassName('Data\TrackingEventType');
        $this->adapter->setEventType($trackingEventTypeEnum::Display());
        $this->adapter->setTrackingParameters(array(
            'sourceRefKey' => '789',
            'invalidParameter' => 'test', // should be ignored
            'sid' => '321',
            'site' => 'internal',
        ));
        $this->assertTrue($this->adapter->applyTracking());
    }
}
