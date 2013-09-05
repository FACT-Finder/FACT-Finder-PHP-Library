<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

class Utf8EncodingConverterTest extends BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\Utf8EncodingConverter the converter under test
     */
    private $encodingConverter;

    public function setUp()
    {
        if (!function_exists('utf8_encode')
            || !function_exists('utf8_decode'))
        {
            $this->markTestSkipped(
              'The built-in utf8 conversion functions are not available.'
            );
        }

        parent::setUp();

        $configuration = FF::getInstance(
            'Core\ManualConfiguration',
            array(
                'pageContentEncoding' => 'ISO-8859-1',
                'clientUrlEncoding' => 'ISO-8859-1'
            )
        );

        $this->encodingConverter = FF::getInstance(
            'Core\Utf8EncodingConverter',
            $this->dic['loggerClass'],
            $configuration
        );

        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testEncodeContentForPage()
    {
        // Input is "ä" in UTF-8
        $utf8Content = "\xC3\xA4";

        // Page content is configured to be ISO-8859-1 encoded.
        $expectedPageContent = "\xE4";

        $this->assertEquals(
            $expectedPageContent,
            $this->encodingConverter->encodeContentForPage($utf8Content)
        );
    }

    public function testDecodeClientUrlData()
    {
        // Client "URL" is "ä" in ISO-8859-1
        $utf16LEstring = "\xE4";

        // Output is expected to be UTF-8
        $expectedUtf8string = "\xC3\xA4";

        $this->assertEquals(
            $expectedUtf8string,
            $this->encodingConverter->decodeClientUrlData($utf16LEstring)
        );
    }

    public function testEncodeClientUrlData()
    {
        // Input is "ä" in UTF-8
        $utf8string = "\xC3\xA4";

        // Client URL is configured to be ISO-8859-1 encoded.
        $expectedUtf16LEstring = "\xE4";

        $this->assertEquals(
            $expectedUtf16LEstring,
            $this->encodingConverter->encodeClientUrlData($utf8string)
        );
    }
}
