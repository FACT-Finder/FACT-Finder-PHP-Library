<?php
namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class ImportTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Adapter\Import
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->adapter = FF::getInstance(
            'Adapter\Import',
            self::$dic['loggerClass'],
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder'],
            self::$dic['encodingConverter']
        );
    }

    public function testDataImport()
    {
        $this->adapter->triggerDataImport();
    }

    public function testSuggestImport()
    {
        $this->adapter->triggerSuggestImport();
    }

    public function testRecommendationImport()
    {
        $this->adapter->triggerRecommendationImport();
    }
}
