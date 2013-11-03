<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class SimilarRecordsTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\SimilarRecords
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\SimilarRecords',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testSimilarRecordLoading()
    {
        $this->adapter->setProductId('123');
        $similarRecords = $this->adapter->getSimilarRecords();

        $this->assertEquals(6, count($similarRecords), 'wrong number of similar records delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $similarRecords[0], 'similar product is no record');
        $this->assertNotEmpty($similarRecords[0], 'first similar record is empty');
        $this->assertEquals('221911', $similarRecords[0]->getId());
        $this->assertEquals('..BMX Bikes..', $similarRecords[0]->getField('Category3'));
    }

    public function testSimilarIDsOnly()
    {
        $this->adapter->setProductId('123');
        $this->adapter->setIdsOnly(true);
        $similarRecords = $this->adapter->getSimilarRecords();

        $this->assertEquals(6, count($similarRecords), 'wrong number of similar records delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $similarRecords[0], 'similar product is no record');
        $this->assertNotEmpty($similarRecords[0], 'first similar record is empty');
        $this->assertEquals('278006', $similarRecords[0]->getId());
    }

    public function testReloadAfterIDsOnly()
    {
        $this->adapter->setProductId('123');
        $this->adapter->setIdsOnly(true);
        $similarIds = $this->adapter->getSimilarRecords();
        $this->adapter->setIdsOnly(false);
        $similarRecords = $this->adapter->getSimilarRecords();

        $this->assertEquals(6, count($similarIds), 'wrong number of similar records delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $similarIds[0], 'similar product is no record');
        $this->assertNotEmpty($similarIds[0], 'first similar record is empty');
        $this->assertEquals('278006', $similarIds[0]->getId());

        $this->assertInstanceOf('FACTFinder\Data\Record', $similarRecords[0], 'similar product is no record');
        $this->assertNotEmpty($similarRecords[0], 'first similar record is empty');
        $this->assertEquals('221911', $similarRecords[0]->getId());
        $this->assertEquals('..BMX Bikes..', $similarRecords[0]->getField('Category3'), 'first similar record does not contain all fields');
    }

    public function testMaxRecordCount()
    {
        $this->adapter->setProductId('123');
        $this->adapter->setIdsOnly(true);
        $this->adapter->setRecordCount(3);
        $similarRecords = $this->adapter->getSimilarRecords();

        $this->assertEquals(3, count($similarRecords), 'wrong number of similar records delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $similarRecords[0], 'similar product is no record');
        $this->assertNotEmpty($similarRecords[0], 'first similar record is empty');
    }

    public function testSimilarAttributesLoading()
    {
        $this->adapter->setProductId('123');
        $similarAttributes = $this->adapter->getSimilarAttributes();

        $this->assertEquals(3, count($similarAttributes), 'wrong number of similar attributes delivered');
        $this->assertEquals('..BMX Bikes..', $similarAttributes['Category3'], 'wrong attribute value delivered');
    }
}
