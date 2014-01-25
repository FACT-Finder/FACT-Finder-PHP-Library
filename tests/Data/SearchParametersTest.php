<?php
namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class SearchParametersTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    public function setUp()
    {
        parent::setUp();
        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testConstructionFromParameters()
    {
        $parameters = FF::getInstance('Util\Parameters');
        $parameters['query'] = 'bmx';
        $parameters['channel'] = 'de';
        $parameters['productsPerPage'] = 12;
        $parameters['filterBrand'] = 'KHE';
        $parameters['filterColor'] = 'green';
        $parameters['sortPrice'] = 'asc';
        $parameters['catalog'] = 'true';

        $searchParameters = FF::getInstance(
            'Data\SearchParameters',
            $parameters
        );

        $this->assertEquals('bmx', $searchParameters->getQuery());
        $this->assertEquals('de', $searchParameters->getChannel());
        $this->assertEquals(12, $searchParameters->getProductsPerPage());
        $this->assertEquals(1, $searchParameters->getCurrentPage());
        $this->assertEquals(10000, $searchParameters->getFollowSearch());

        $this->assertEquals(array('Brand' => 'KHE', 'Color' => 'green'),
                            $searchParameters->getFilters());
        $this->assertEquals(array('Price' => 'asc'),
                            $searchParameters->getSortings());

        $this->assertTrue($searchParameters->isNavigationEnabled());
    }
}
