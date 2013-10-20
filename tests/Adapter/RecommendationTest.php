<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class RecommendationTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\Recommendation
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\Recommendation',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testRecommendationLoading()
    {
        $this->adapter->setProductIds('274036');
        $recommendations = $this->adapter->getRecommendations();

        $this->assertEquals(1, count($recommendations), 'wrong number of recommendations delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $recommendations[0], 'recommended product is no record');
        $this->assertNotEmpty($recommendations[0], 'first recommended record is empty');
        $this->assertEquals('274035', $recommendations[0]->getId(), 'wrong id delivered for first recommended record');
    }

    public function testIdsOnly()
    {
        $this->adapter->setProductIds('274036');
        $this->adapter->setIdsOnly(true);
        $recommendations = $this->adapter->getRecommendations();

        $this->assertEquals(1, count($recommendations), 'wrong number of recommendations delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $recommendations[0], 'recommended product is no record');
        $this->assertNotEmpty($recommendations[0], 'first recommended record is empty');
        $this->assertEquals('274035', $recommendations[0]->getId(), 'wrong id delivered for first recommended record');
    }

    public function testReload()
    {
        $this->adapter->setProductIds('274036');
        $recommendations = $this->adapter->getRecommendations();
        $this->assertEquals('274035', $recommendations[0]->getId(), 'wrong id delivered for first recommended record');
        $this->adapter->setProductIds('233431');
        $recommendations = $this->adapter->getRecommendations();
        $this->assertEquals('327212', $recommendations[0]->getId(), 'wrong id delivered for first recommended record');
    }

    public function testReloadAfterIdsOnly()
    {
        $this->adapter->setProductIds('274036');
        $this->adapter->setIdsOnly(true);
        $recommendations = $this->adapter->getRecommendations();
        $this->adapter->setIdsOnly(false);
        $recommendations = $this->adapter->getRecommendations();
        $this->assertNotNull($recommendations[0]->getField('Description'), 'did not load full recommendation record');
    }

    public function testMultiProductRecommendationLoading()
    {
        $this->adapter->setProductIds(array('274036', '233431'));
        $recommendations = $this->adapter->getRecommendations();

        $this->assertEquals(1, count($recommendations), 'wrong number of recommendations delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $recommendations[0], 'recommended product is no record');
        $this->assertNotEmpty($recommendations[0], 'first recommended record is empty');
        $this->assertEquals('225052', $recommendations[0]->getID(), 'wrong id delivered for first recommended record');
    }
}
