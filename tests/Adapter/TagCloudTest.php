<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class TagCloudTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\TagCloud
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        // For the request parser to retrieve
        $_SERVER['REQUEST_URI'] = '/index.php';

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\TagCloud',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testGetTagCloud()
    {
        $tagCloud = $this->adapter->getTagCloud();

        $this->assertEquals(5, count($tagCloud), 'wrong number of tag queries delivered');
        $this->assertInstanceOf('FACTFinder\Data\TagQuery', $tagCloud[0], 'tag cloud element is no tag query');
        $this->assertEquals('28+zoll+damen', $tagCloud[0]->getLabel(), 'wrong query delivered for first tag query');
        $this->assertEquals('/index.php?keywords=28%2Bzoll%2Bdamen', $tagCloud[0]->getUrl(), 'wrong url delivered for first tag query');
        $this->assertEquals(0.561, $tagCloud[0]->getWeight(), 'wrong weight delivered for first tag query', 0.0001);
        $this->assertEquals(1266, $tagCloud[0]->getSearchCount(), 'wrong search count delivered for first tag query');
        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
    }

    public function testDefaultWordCount()
    {
        $this->adapter->setWordCount(-1);
        $tagCloud = $this->adapter->getTagCloud();
        $this->assertEquals(5, count($tagCloud), 'wrong number of tag queries delivered');
    }

    public function testWordCount()
    {
        $this->adapter->setWordCount(3);
        $tagCloud = $this->adapter->getTagCloud();

        $this->assertEquals(3, count($tagCloud), 'wrong number of tag queries delivered');
        $this->assertInstanceOf('FACTFinder\Data\TagQuery', $tagCloud[0], 'tag cloud element is no tag query');
        $this->assertEquals('28+zoll+damen', $tagCloud[0]->getLabel(), 'wrong query delivered for first tag query');
        $this->assertEquals('/index.php?keywords=28%2Bzoll%2Bdamen', $tagCloud[0]->getUrl(), 'wrong url delivered for first tag query');
        $this->assertEquals(0.561, $tagCloud[0]->getWeight(), 'wrong weight delivered for first tag query', 0.0001);
        $this->assertEquals(1266, $tagCloud[0]->getSearchCount(), 'wrong search count delivered for first tag query');
        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
    }

    public function testSelectedTagQuery()
    {
        $tagCloud = $this->adapter->getTagCloud();

        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[1]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[2]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[3]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[4]->isSelected(), 'first tag query should not be selected');

        $tagCloud = $this->adapter->getTagCloud('bmx');

        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[1]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[2]->isSelected(), 'first tag query should not be selected');
        $this->assertTrue($tagCloud[3]->isSelected(), 'first tag query should be selected');
        $this->assertFalse($tagCloud[4]->isSelected(), 'first tag query should not be selected');

        $tagCloud = $this->adapter->getTagCloud('bikes');

        $this->assertFalse($tagCloud[0]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[1]->isSelected(), 'first tag query should not be selected');
        $this->assertTrue($tagCloud[2]->isSelected(), 'first tag query should be selected');
        $this->assertFalse($tagCloud[3]->isSelected(), 'first tag query should not be selected');
        $this->assertFalse($tagCloud[4]->isSelected(), 'first tag query should not be selected');
    }
}
