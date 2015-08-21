<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class SearchTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var \FACTFinder\Adapter\Search
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        // For the request parser to retrieve
        $_SERVER['REQUEST_URI'] = '/index.php';
        $_SERVER['QUERY_STRING'] = 'query=bmx';

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\Search',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testGetResult()
    {
        $result = $this->adapter->getResult();

        $this->assertInstanceOf('FACTFinder\Data\Result', $result);
        $this->assertEquals(66, $result->getFoundRecordsCount());
        $this->assertEquals('WOwfiHGNS', $result->getRefKey());
        $this->assertEquals(1, count($result));

        $record = $result[0];
        $this->assertEquals('278003', $record->getId());
        $this->assertEquals('KHE', $record->getField('Brand'));
        $this->assertEquals(13, $record->getPosition());
        $this->assertEquals('/KHE-Shotgun-ST-gun-grey-278003.html', $record->getSeoPath());
        $this->assertEquals(0, count($record->getKeywords()));
        $this->assertEquals(97.98, $record->getSimilarity(), 1e-10);
    }
    
    public function testReloadAfterSetIdsOnly()
    {
        $this->adapter->setIdsOnly(true);
        $result = $this->adapter->getResult();

        $this->assertInstanceOf('FACTFinder\Data\Result', $result);
        $this->assertEquals(66, $result->getFoundRecordsCount());
        $this->assertEquals('WOwfiHGNS', $result->getRefKey());
        $this->assertEquals(1, count($result));

        $record = $result[0];
        $this->assertEquals('278003', $record->getId());
        $this->assertNull($record->getField('Brand'));
        
        //idsOnly=false should be reloaded with full detailed records
        $this->adapter->setIdsOnly(false);
        $result = $this->adapter->getResult();
        $record = $result[0];
        $this->assertEquals('278003', $record->getId());
        $this->assertEquals('KHE', $record->getField('Brand'));
    }

    public function testGetStatus()
    {
        $searchStatusEnum = FF::getClassName('Data\SearchStatus');
        $this->assertEquals($searchStatusEnum::RecordsFound(), $this->adapter->getStatus());
    }

    public function testGetSearchTimeInfo()
    {
        $this->assertFalse($this->adapter->isSearchTimedOut());
    }

    public function testGetAfterSearchNavigation()
    {
        $asn = $this->adapter->getAfterSearchNavigation();

        $this->assertInstanceOf('FACTFinder\Data\AfterSearchNavigation', $asn);
        $this->assertEquals(4, count($asn));
        $this->assertTrue($asn->hasPreviewImages());

        $this->assertTrue($asn[0]->isRegularStyle());
        $this->assertEquals('Für Wen?', $asn[0]->getName());
        $this->assertEquals(5, $asn[0]->getDetailedLinkCount());
        $this->assertTrue($asn[0]->hasPreviewImages());
        $this->assertTrue($asn[0]->hasSelectedItems());

        $this->assertEquals(3, count($asn[0]));
        $this->assertTrue($asn[0][0]->isSelected());
        $this->assertFalse($asn[0][1]->isSelected());
        $this->assertFalse($asn[0][2]->isSelected());
        $this->assertEquals(1, $asn[0][2]->getMatchCount());
        $this->assertFalse($asn[0][0]->hasPreviewImage());
        $this->assertTrue($asn[0][1]->hasPreviewImage());
        $this->assertFalse($asn[0][2]->hasPreviewImage());
        $this->assertEquals('image.png', $asn[0][1]->getPreviewImage());

        $this->assertTrue($asn[1]->isTreeStyle());
        $this->assertEquals('Kategorie', $asn[1]->getName());
        $this->assertEquals(5, $asn[1]->getDetailedLinkCount());
        $this->assertEquals(3, count($asn[1]));
        $this->assertTrue($asn[1][0]->isSelected());
        $this->assertTrue($asn[1][1]->isSelected());
        $this->assertTrue($asn[1][2]->isSelected());
        $this->assertEquals(0, $asn[1][2]->getMatchCount());
        $this->assertEquals(2, $asn[1][2]->getClusterLevel());

        $this->assertTrue($asn[2]->isMultiSelectStyle());
        $this->assertFalse($asn[2]->hasSelectedItems());
        $this->assertFalse($asn[2][0]->isSelected());
        $this->assertFalse($asn[2][1]->isSelected());
        $this->assertFalse($asn[2][2]->isSelected());

        $this->assertTrue($asn[3]->isSliderStyle());
        $this->assertEquals('Bewertung', $asn[3]->getName());
        $this->assertEquals('€', $asn[3]->getUnit());
        $this->assertEquals(5, $asn[3]->getDetailedLinkCount());
        $slider = $asn[3][0];
        $this->assertEquals(5, $slider->getAbsoluteMaximum());
        $this->assertEquals(4, $slider->getAbsoluteMinimum());
        $this->assertEquals(5, $slider->getSelectedMaximum());
        $this->assertEquals(4, $slider->getSelectedMinimum());
        $this->assertEquals('RatingAverage', $slider->getFieldName());
        $this->assertFalse($slider->isSelected());
        $this->assertEquals('/index.php?seoPath=%2Fbmx%2Fq&filterCategory2=BMX&filterCategory1=Fahrr%E4der&followSearch=9798&filterRatingAverage=', $slider->getBaseUrl());
        $this->assertEquals('/index.php?seoPath=%2Fbmx%2Fq&filterCategory2=BMX&filterCategory1=Fahrr%E4der&followSearch=9798&filterRatingAverage=4-5', $slider->getUrl());

        unset($asn[0][1]);
        $this->assertTrue($asn->hasPreviewImages());
    }

    public function testGetResultsPerPageOptions()
    {
        $rppo = $this->adapter->getResultsPerPageOptions();

        $this->assertNotEmpty($rppo, 'results per page options should be loaded');
        $this->assertInstanceOf('FACTFinder\Data\ResultsPerPageOptions', $rppo);

        $this->assertEquals(3, count($rppo));
        $this->assertFalse($rppo[0]->isSelected());
        $this->assertTrue($rppo[1]->isSelected());
        $this->assertSame($rppo[0], $rppo->getDefaultOption());
        $this->assertSame($rppo[1], $rppo->getSelectedOption());
        $this->assertEquals('12', $rppo[0]->getLabel());
    }

    public function testPagingLoading()
    {
        $paging = $this->adapter->getPaging();

        $this->assertInstanceOf('FACTFinder\Data\Paging', $paging);
        $this->assertEquals(6, $paging->getPageCount());
        $this->assertEquals(6, count($paging));
        $this->assertFalse($paging[0]->isSelected());
        $this->assertTrue($paging[1]->isSelected());
        $this->assertFalse($paging[2]->isSelected());
        $this->assertEquals(1, $paging->getFirstPage()->getPageNumber());
        $this->assertEquals('1', $paging->getFirstPage()->getLabel());
        $this->assertEquals(6, $paging->getLastPage()->getPageNumber());
        $this->assertEquals('6', $paging->getLastPage()->getLabel());
        $this->assertEquals(1, $paging->getPreviousPage()->getPageNumber());
        $this->assertEquals('paging.previous', $paging->getPreviousPage()->getLabel());
        $this->assertEquals(2, $paging->getCurrentPage()->getPageNumber());
        $this->assertEquals('2', $paging->getCurrentPage()->getLabel());
        $this->assertEquals(3, $paging->getNextPage()->getPageNumber());
        $this->assertEquals('paging.next', $paging->getNextPage()->getLabel());
    }

    public function testSortingLoading()
    {
        $sorting = $this->adapter->getSorting();

        $this->assertInstanceOf('FACTFinder\Data\Sorting', $sorting);
        $this->assertEquals(5, count($sorting));
        $this->assertInstanceOf("FACTFinder\Data\Item", $sorting[0]);
        $this->assertEquals('sort.relevanceDescription', $sorting[0]->getLabel());
        $this->assertTrue($sorting[0]->isSelected());
        $this->assertFalse($sorting[1]->isSelected());
    }

    public function testBreadCrumbLoading()
    {
        $breadCrumbs = $this->adapter->getBreadCrumbTrail();

        $this->assertInstanceOf('FACTFinder\Data\BreadCrumbTrail', $breadCrumbs);
        $this->assertEquals(3, count($breadCrumbs));
        $this->assertInstanceOf('FACTFinder\Data\BreadCrumb', $breadCrumbs[0]);
        $this->assertEquals('bmx', $breadCrumbs[0]->getLabel());
        $this->assertTrue($breadCrumbs[0]->isSearchBreadCrumb());
        $this->assertEquals('Category1', $breadCrumbs[1]->getFieldName());
        $this->assertTrue($breadCrumbs[1]->isFilterBreadCrumb());
    }

    public function testEmptyCampaigns()
    {
        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertEquals(0, count($campaigns));
    }

    public function testCampaignLoading()
    {
        $this->adapter->setQuery('campaigns');

        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertInstanceOf('FACTFinder\Data\Campaign', $campaigns[0]);

        $this->assertTrue($campaigns->hasRedirect());
        $this->assertEquals('http://www.fact-finder.de', $campaigns->getRedirectUrl());

        $this->assertTrue($campaigns->hasFeedback());
        $expectedFeedback = implode(PHP_EOL, array("test feedback 1", "test feedback 2"));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('html header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('9'));
        $expectedFeedback = "test feedback 3";
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('below header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('6'));

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('17552', $products[0]->getId());
        $this->assertEquals('..Fahrräder..', $products[0]->getField('Category1'));

        $this->assertTrue($campaigns->hasActiveQuestions());
        $questions = $campaigns->getActiveQuestions();
        $this->assertEquals(1, count($questions));
        $this->assertEquals('question text', $questions[0]->getText());
        $answers = $questions[0]->getAnswers();
        $this->assertEquals(2, count($answers));
        $this->assertEquals('answer text 1', $answers[0]->getText());
        $this->assertFalse($answers[0]->hasFollowUpQuestions());
        $this->assertEquals('answer text 2', $answers[1]->getText());
        $this->assertFalse($answers[1]->hasFollowUpQuestions());

        $this->assertFalse($campaigns->hasAdvisorTree());
        $advisorTree = $campaigns->getAdvisorTree();
        $this->assertEquals(0, count($advisorTree));
    }

    public function testNoError()
    {
        $this->assertNull($this->adapter->getError());
        $this->assertNull($this->adapter->getStackTrace());
    }

    public function testError()
    {
        $this->adapter->setQuery('error');

        $this->assertEquals('500', $this->adapter->getError());
        $this->assertEquals('stacktrace', $this->adapter->getStackTrace());
    }

    public function testGetFollowSearchValue()
    {
        $this->assertEquals(9798, $this->adapter->getFollowSearchValue());
    }

    public function testArticleNumberSearchStatus()
    {
        $this->adapter->setQuery('278003');
        $articleNumberSearchStatusEnum = FF::getClassName('Data\ArticleNumberSearchStatus');
        $this->assertEquals($articleNumberSearchStatusEnum::IsArticleNumberResultFound(), $this->adapter->getArticleNumberStatus());
    }

    public function testNoArticleNumberSearchStatus()
    {
        $articleNumberSearchStatusEnum = FF::getClassName('Data\ArticleNumberSearchStatus');
        $this->assertEquals($articleNumberSearchStatusEnum::IsNoArticleNumberSearch(), $this->adapter->getArticleNumberStatus());
    }
}
