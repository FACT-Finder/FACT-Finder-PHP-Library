<?php
namespace FACTFinder\Test\Core;

use FACTFinder\Loader as FF;

class ParametersConverterTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\ParametersConverter the converter under test
     */
    private $parametersConverter;

    public function setUp()
    {
        parent::setUp();

        $this->parametersConverter = FF::getInstance(
            'Core\ParametersConverter',
            self::$dic['loggerClass'],
            self::$dic['configuration']
        );

        $loggerClass = self::$dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testClientToServerConversion()
    {
        $clientParameters = FF::getInstance(
            'Util\Parameters',
            array(
                'keywords' => 'test',
                'username' => 'admin',
                'productsPerPage' => '12',
                'shop' => 'main',
                'filterSize' => '10',
            )
        );

        $expectedServerParameters = array(
            'query' => 'test',
            'productsPerPage' => '12',
            'channel' => 'de',
            'filterSize' => '10',
        );

        $actualServerParameters = $this->parametersConverter
                                       ->convertClientToServerParameters(
                                            $clientParameters
                                        );

        $this->assertEquals(
            $expectedServerParameters,
            $actualServerParameters->getArray()
        );
    }

    public function testOverwriteChannel()
    {
        $clientParameters = FF::getInstance(
            'Util\Parameters',
            array(
                'channel' => 'en',
            )
        );

        $expectedServerParameters = array(
            'channel' => 'en',
        );

        $actualServerParameters = $this->parametersConverter
                                       ->convertClientToServerParameters(
                                            $clientParameters
                                        );

        $this->assertEquals(
            $expectedServerParameters,
            $actualServerParameters->getArray()
        );
    }

    public function testServerToClientConversion()
    {
        $serverParameters = FF::getInstance(
            'Util\Parameters',
            array(
                'query' => 'test',
                'username' => 'admin',
                'format' => 'xml',
                'xml' => 'true',
                'timestamp' => '123456789',
                'password' => 'test',
                'channel' => 'de',
                'productsPerPage' => '12',
                'any' => 'something',
            )
        );

        $expectedClientParameters = array(
            'keywords' => 'test',
            'productsPerPage' => '12',
        );

        $actualClientParameters = $this->parametersConverter
                                       ->convertServerToClientParameters(
                                            $serverParameters
                                        );

        $this->assertEquals(
            $expectedClientParameters,
            $actualClientParameters->getArray()
        );
    }
}
