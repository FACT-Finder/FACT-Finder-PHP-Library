<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

/**
 * This is named BaseTestCASE so that PHPUnit does not look for tests inside
 * this class.
 * @package default
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FACTFinder\Util\Pimple Dependency injection container
     */
    protected $dic;

    public function setUp()
    {
        $this->dic = FF::getInstance('Util\Pimple');

        $logClass = FF::getClassName('Util\Log4PhpLogger');
        $logClass::configure(RESOURCES_DIR.DS.'log4php.xml');
        $this->dic['loggerClass'] = $logClass;

        $this->dic['configuration'] = $this->dic->share(function($c) {
            return FF::getInstance(
                'Core\XmlConfiguration',
                RESOURCES_DIR.DS.'config.xml',
                'test'
            );
        });

        $this->dic['encodingConverter'] = $this->dic->share(function($c) {
            if (extension_loaded('iconv'))
                $type = 'Core\IConvEncodingConverter';
            else if (function_exists('utf8_encode')
                     && function_exists('utf8_decode'))
                $type = 'Core\Utf8EncodingConverter';
            else
                throw new Exception('No encoding conversion available.');

            return FF::getInstance(
                $type,
                $c['loggerClass'],
                $c['configuration']
            );
        });
    }
}
