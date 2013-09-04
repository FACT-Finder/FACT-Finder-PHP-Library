<?php
namespace FACTFinder\Core;

/**
 * Takes care of differences in encoding between different participants of the
 * communication. Internal to the library all strings are encoded as UTF-8, so
 * the methods of this class are only for converting to and from UTF-8. The
 * source and target encodings are determined by the configuration.
 * This abstract class does not specify how the actual conversion of a single
 * string is done. Create a subclass to implement the conversion method.
 */
abstract class AbstractEncodingConverter
{
    const LIBRARY_ENCODING = 'UTF-8';

    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $pageContentEncoding;
    /**
     * @var string
     */
    protected $clientUrlEncoding;

    /**
     * @param string $loggerClass Class name of logger to use. The class should
     *                            implement FACTFinder\Util\LoggerInterface.
     * @param ConfigurationInterface $config Configuration object to use.
     */
    function __construct(
        $loggerClass,
        ConfigurationInterface $config
    ) {
        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->pageContentEncoding = $config->getPageContentEncoding();
        $this->clientUrlEncoding = $config->getClientUrlEncoding();
    }

    abstract protected function convert($inCharset, $outCharset, $string);

    /**
     * Converts data held by the library for use on the rendered page.
     * Hence, it converts from the library's encoding (UTF-8) to the configured
     * page content encoding.
     * @param string $content
     * @return string
     */
    public function encodeContentForPage($content)
    {
        return $this->convert(
            self::LIBRARY_ENCODING,
            $this->pageContentEncoding,
            $content
        );
    }

    /**
     * Converts data obtained from the client URL for use within the library.
     * Hence, it converts fromthe configured client URL encoding to the
     * library's encoding (UTF-8).
     * @param string $string Data obtained from the client URL. Note that this
     *        data should already be URL decoded.
     * @return string
     */
    public function decodeClientUrlData($string)
    {
        return $this->convert(
            $this->clientUrlEncoding,
            self::LIBRARY_ENCODING,
            $string
        );
    }

    /**
     * Converts data held by the library for use in a client URL.
     * Hence, it converts fromthe configured client URL encoding to the
     * library's encoding (UTF-8).
     * @param string $string Data to be used in the client URL. Note that this
     *        data should not yet be URL encoded.
     * @return string
     */
    public function encodeClientUrlData($string)
    {
        return $this->convert(
            self::LIBRARY_ENCODING,
            $this->clientUrlEncoding,
            $string
        );
    }
}
