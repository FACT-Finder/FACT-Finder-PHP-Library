<?php
namespace FACTFinder\Test;

use FACTFinder\Loader as FF;

class IConvEncodingConverterTest extends BaseTestCase
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Core\IConvEncodingConverter the converter under test
     */
    private $encodingConverter;

    public function setUp()
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped(
              'The iconv module has not been loaded.'
            );
        }

        parent::setUp();

        $config = FF::getInstance(
            'Core\ManualConfiguration',
            array(
                'pageContentEncoding' => 'UTF-16BE',
                'clientUrlEncoding' => 'UTF-16LE'
            )
        );

        $this->encodingConverter = FF::getInstance(
            'Core\IConvEncodingConverter',
            $this->dic['loggerClass'],
            $config
        );

        $loggerClass = $this->dic['loggerClass'];
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function testEncodeContentForPage()
    {
        // Input is "ä" in UTF-8
        $utf8Content = "\xC3\xA4";

        // Page content is configured to be UTF-16BE encoded.
        $expectedPageContent = "\x00\xE4";

        $this->assertEquals(
            $expectedPageContent,
            $this->encodingConverter->encodeContentForPage($utf8Content)
        );
    }

    public function testDecodeClientUrlData()
    {
        // Client "URL" is "ä" in UTF-16LE
        $utf16LEstring = "\xE4\x00";

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

        // Client URL is configured to be UTF-16LE encoded.
        $expectedUtf16LEstring = "\xE4\x00";

        $this->assertEquals(
            $expectedUtf16LEstring,
            $this->encodingConverter->encodeClientUrlData($utf8string)
        );
    }
}
