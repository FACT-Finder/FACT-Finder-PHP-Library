<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

class ParametersConverterTest extends BaseTestCase
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
            $this->dic['loggerClass'],
            $this->dic['configuration']
        );

        $loggerClass = $this->dic['loggerClass'];
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
            )
        );

        $expectedServerParameters = array(
            'query' => 'test',
            'productsPerPage' => '12',
            'channel' => 'de',
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
                'channel' => 'en'
            )
        );

        $expectedServerParameters = array(
            'channel' => 'en'
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
                'productsPerPage' => '12'
            )
        );

        $expectedClientParameters = array(
            'keywords' => 'test',
            'productsPerPage' => '12',
            'test' => 'value'
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
