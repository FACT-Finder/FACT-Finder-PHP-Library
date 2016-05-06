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
    protected static $dic;

    public static function setUpBeforeClass()
    {
        $logClass = FF::getClassName('Util\Log4PhpLogger');
        $logClass::configure(RESOURCES_DIR.DS.'log4php.xml');

        // Set up dependency injection container (Pimple)

        self::$dic = FF::getInstance('Util\Pimple');

        self::$dic['loggerClass'] = $logClass;

        if (strpos(static::class, 'ArrayConfiguration')) {
            self::$dic['configuration'] = function($c) {
                $config = include RESOURCES_DIR.DS.'config.php';
                return FF::getInstance(
                    'Core\ArrayConfiguration',
                    $config,
                    'test'
                );
            };
        } else {
            self::$dic['configuration'] = function ($c) {
                return FF::getInstance(
                    'Core\XmlConfiguration',
                    RESOURCES_DIR . DS . 'config.xml',
                    'test'
                );
            };
        }

        // $this cannot be passed into closures before PHP 5.4
        //$that = $this;
        self::$dic['encodingConverter'] = function($c) {
            if (extension_loaded('iconv'))
                $type = 'Core\IConvEncodingConverter';
            else if (function_exists('utf8_encode')
                     && function_exists('utf8_decode'))
                $type = 'Core\Utf8EncodingConverter';
            else
                return;
            //TODO: Skip test if no conversion method is available.
            //    $that->markTestSkipped('No encoding conversion available.');

            return FF::getInstance(
                $type,
                $c['loggerClass'],
                $c['configuration']
            );
        };

        self::$dic['serverUrlBuilder'] = function($c) {
            return FF::getInstance(
                'Core\Server\UrlBuilder',
                $c['loggerClass'],
                $c['configuration']
            );
        };

        self::$dic['clientUrlBuilder'] = function($c) {
            return FF::getInstance(
                'Core\Client\UrlBuilder',
                $c['loggerClass'],
                $c['configuration'],
                $c['requestParser'],
                $c['encodingConverter']
            );
        };

        self::$dic['curlStub'] = function($c) {
            return FF::getInstance('Util\CurlStub');
        };

        self::$dic['dataProvider'] = function($c) {
            $dataProvider = FF::getInstance(
                'Core\Server\FileSystemDataProvider',
                $c['loggerClass'],
                $c['configuration']
            );

            $dataProvider->setFileLocation(RESOURCES_DIR . DS . 'responses');

            return $dataProvider;
        };

        self::$dic['requestFactory'] = function($c) {
            $requestFactory = FF::getInstance(
                'Core\Server\FileSystemRequestFactory',
                $c['loggerClass'],
                $c['configuration'],
                $c['requestParser']->getRequestParameters()
            );

            $requestFactory->setFileLocation(RESOURCES_DIR . DS . 'responses');

            return $requestFactory;
        };

        self::$dic['request'] = self::$dic->factory(function($c) {
            return $c['requestFactory']->getRequest();
        });

        self::$dic['requestParser'] = function($c) {
            return FF::getInstance(
                'Core\Client\RequestParser',
                $c['loggerClass'],
                $c['configuration'],
                $c['encodingConverter']
            );
        };
    }
}
