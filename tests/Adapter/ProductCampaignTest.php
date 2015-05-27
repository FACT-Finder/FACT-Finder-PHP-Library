<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class ProductCampaignTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\ProductCampaign
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\ProductCampaign',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder'],
            self::$dic['encodingConverter']
        );

    }

    public function testProductCampaignLoading()
    {
        $productIds = array();
        $productIds[] = 123;
        $productIds[] = 456; // should be ignored
        $this->adapter->setProductIDs($productIds);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertInstanceOf('FACTFinder\Data\Campaign', $campaigns[0]);

        $this->assertTrue($campaigns->hasRedirect());
        $this->assertEquals('http://www.fact-finder.de', $campaigns->getRedirectUrl());

        $this->assertTrue($campaigns->hasFeedback());
        $expectedFeedback = "test feedback";
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('html header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('9'));

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertEquals('KHE', $products[0]->getField('Brand'));

        $this->assertFalse($campaigns->hasActiveQuestions());
    }

    public function testShoppingCartCampaignLoading()
    {
        $productIds = array();
        $productIds[] = 456;
        $productIds[] = 789;
        $this->adapter->makeShoppingCartCampaign();
        $this->adapter->setProductIDs($productIds);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertInstanceOf('FACTFinder\Data\Campaign', $campaigns[0]);

        $this->assertTrue($campaigns->hasRedirect());
        $this->assertEquals('http://www.fact-finder.de', $campaigns->getRedirectUrl());

        $this->assertTrue($campaigns->hasFeedback());
        $expectedFeedback = "test feedback";
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('html header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('9'));

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertEquals('KHE', $products[0]->getField('Brand'));

        $this->assertFalse($campaigns->hasActiveQuestions());
    }
}
