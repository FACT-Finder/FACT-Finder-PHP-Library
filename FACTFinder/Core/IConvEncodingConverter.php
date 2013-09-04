<?php
namespace FACTFinder\Core;

/**
 * Implements the AbstractEncodingConverter using the iconv module.
 */
class IConvEncodingConverter extends AbstractEncodingConverter
{
    function __construct(
        $loggerClass,
        ConfigurationInterface $config
    ) {
        parent::__construct($loggerClass, $config);
        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    protected function convert($inCharset, $outCharset, $string)
    {
        if ($inCharset == $outCharset
            || empty($inCharset)
            || empty($outCharset)
        ) {
            return $string;
        }

        // See http://www.php.net/manual/en/function.iconv.php for more
        // information on '//TRANSLIT'.
        $result = iconv($inCharset, $outCharset.'//TRANSLIT', $string);

        if ($result === false)
        {
            $this->log->warn(
                "Conversion from $inCharset to $outCharset not possible. " +
                "The string is still encoded with $inCharset."
            );
            $result = $string;
        }

        return $result;
    }
}
