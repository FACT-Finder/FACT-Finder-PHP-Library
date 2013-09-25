<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class SuggestTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\Suggest
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $_SERVER['REQUEST_URI'] = '/index.php';
        // For the request parser to retrieve
        $_SERVER['QUERY_STRING'] = 'query=bmx';

        $this->dataProvider = FF::getInstance(
            'Core\Server\FileSystemDataProvider',
            self::$dic['loggerClass'],
            self::$dic['configuration']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\Suggest',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
    }

    public function testGetSuggestions()
    {
        $suggestions = $this->adapter->getSuggestions();
        $this->assertEquals(3, count($suggestions), 'wrong number of suggest queries delivered');
        $this->assertInstanceOf('FACTFinder\Data\SuggestQuery', $suggestions[0], 'suggestion element is no suggest query');
        $this->assertEquals('Verde BMX', $suggestions[0]->getLabel(), 'wrong query delivered for first suggest item');
        $this->assertEquals('/index.php?filterBrand=Verde%20BMX&ignoreForCache%5B0%5D=queryFromSuggest&ignoreForCache%5B1%5D=userInput&queryFromSuggest=true&userInput=bmx&keywords=Verde%20BMX%20%2A', $suggestions[0]->getUrl(), 'wrong url delivered for first suggest item');
        $this->assertEquals('8blKVw-P5', $suggestions[0]->getRefKey(), 'wrong ref key delivered for first suggest item');
        $this->assertEquals('brand', $suggestions[0]->getType(), 'wrong type delivered for first suggest item');
        $this->assertEquals('category', $suggestions[1]->getType(), 'wrong type delivered for second suggest item');
        $this->assertEquals('productName', $suggestions[2]->getType(), 'wrong type delivered for third suggest item');
    }
}
