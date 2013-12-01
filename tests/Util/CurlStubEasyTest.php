<?php
namespace FACTFinder\Test\Util;

use FACTFinder\Loader as FF;

/**
 * Tests the parts of CurlStub that correspond to cURL's easy interface.
 */
class CurlStubEasyTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\CurlStub
     */
    protected $curlStub;

    const DEFAULT_RESPONSE = 'default response';
    const DEFAULT_ERRORCODE = CURLE_COULDNT_RESOLVE_HOST;
    const DEFAULT_ERRORMESSAGE = 'CURLE_COULDNT_RESOLVE_HOST';

    const RETURN_RESPONSE = 1;
    const RETURN_ERRORCODE = 2;
    const RETURN_ERRORMESSAGE = 3;
    const RETURN_INFO = 4;


    public function setUp()
    {
        $this->curlStub = FF::getInstance('Util\CurlStub');
        $this->curlStub->setResponse(self::DEFAULT_RESPONSE);
        $this->curlStub->setErrorCode(self::DEFAULT_ERRORCODE);
    }

    public function testSetResponse()
    {
        $actualResponse = $this->getResponseFromCurl();

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
    }

    public function testReturnTransfer()
    {
        $curl = $this->curlStub;

        $ch = $curl->init();
        $curl->setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $actualResponse = $curl->exec($ch);
        $curl->close($ch);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
    }

    public function testSetResponseForSpecificUrl()
    {
        $curl = $this->curlStub;

        $expectedResponse = 'page found';
        $url = 'http://www.google.com';
        $requiredOptions = array(
            CURLOPT_URL => $url
        );
        $curl->setResponse($expectedResponse, $requiredOptions);

        // Setting no URL should give default response
        $actualResponse = $this->getResponseFromCurl();

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);

        // Setting correct URL should give set up response
        $actualResponse = $this->getResponseFromCurl($url);

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testSetResponsesForMultipleUrls()
    {
        $curl = $this->curlStub;

        $expectedResponse1 = 'google page found';
        $url1 = 'http://www.google.com';
        $expectedResponse2 = 'bing page found';
        $url2 = 'http://www.bing.com';

        $requiredOptions1 = array(
            CURLOPT_URL => $url1
        );
        $curl->setResponse($expectedResponse1, $requiredOptions1);
        $requiredOptions2 = array(
            CURLOPT_URL => $url2
        );
        $curl->setResponse($expectedResponse2, $requiredOptions2);

        // Setting wrong URL should give default response
        $actualResponse = $this->getResponseFromCurl('http://www.yahoo.com');

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);

        // Setting correct URLs should give corresponding responses
        $actualResponse = $this->getResponseFromCurl($url1);

        $this->assertEquals($expectedResponse1, $actualResponse);

        $actualResponse = $this->getResponseFromCurl($url2);

        $this->assertEquals($expectedResponse2, $actualResponse);
    }

    public function testSetResponsesForMultipleOptions()
    {
        $curl = $this->curlStub;

        $expectedResponse1 = 'google default page found';
        $url1 = 'http://www.google.com';
        $expectedResponse2 = 'google page for chrome found';
        $url2 = $url1;
        $userAgent2 = 'Chrome/22.0.1207.1';
        $expectedResponse3 = 'google page for safari found';
        $url3 = $url1;
        $userAgent3 = 'Safari/537.1';
        $expectedResponse4 = 'google page for chrome from mail site found';
        $url4 = $url1;
        $userAgent4 = $userAgent2;
        $referer4 = 'http://mail.google.com';

        $requiredOptions1 = array(
            CURLOPT_URL => $url1
        );
        $curl->setResponse($expectedResponse1, $requiredOptions1);
        $requiredOptions2 = array(
            CURLOPT_URL => $url2,
            CURLOPT_USERAGENT => $userAgent2
        );
        $curl->setResponse($expectedResponse2, $requiredOptions2);
        $requiredOptions3 = array(
            CURLOPT_URL => $url3,
            CURLOPT_USERAGENT => $userAgent3
        );
        $curl->setResponse($expectedResponse3, $requiredOptions3);
        $requiredOptions4 = array(
            CURLOPT_URL => $url4,
            CURLOPT_USERAGENT => $userAgent4,
            CURLOPT_REFERER => $referer4
        );
        $curl->setResponse($expectedResponse4, $requiredOptions4);

        $actualResponse = $this->getResponseFromCurl($url1);

        $this->assertEquals($expectedResponse1, $actualResponse);

        $actualResponse = $this->getResponseFromCurl($url2, $requiredOptions2);

        $this->assertEquals($expectedResponse2, $actualResponse);

        $actualResponse = $this->getResponseFromCurl($url3, $requiredOptions3);

        $this->assertEquals($expectedResponse3, $actualResponse);

        $actualResponse = $this->getResponseFromCurl($url4, $requiredOptions4);

        $this->assertEquals($expectedResponse4, $actualResponse);

        $tooSpecificOptions = array(
            CURLOPT_URL => $url1,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64)"
        );

        $actualResponse = $this->getResponseFromCurl($url1, $tooSpecificOptions);

        $this->assertEquals($expectedResponse1, $actualResponse);

        $unmatchedOptions = array(
            CURLOPT_REFERER => $referer4
        );

        $actualResponse = $this->getResponseFromCurl(null, $unmatchedOptions);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);

        $actualResponse = $this->getResponseFromCurl('http://www.yahoo.com', $unmatchedOptions);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
    }

    public function testSetErrorCode()
    {
        $curl = $this->curlStub;

        $expectedErrorCode1 = CURLE_UNSUPPORTED_PROTOCOL;
        $url1 = 'svn://www.google.com';
        $expectedErrorCode2 = CURLE_OK;
        $url2 = 'http://www.google.com';

        $requiredOptions1 = array(
            CURLOPT_URL => $url1
        );
        $curl->setErrorCode($expectedErrorCode1, $requiredOptions1);
        $requiredOptions2 = array(
            CURLOPT_URL => $url2
        );
        $curl->setErrorCode($expectedErrorCode2, $requiredOptions2);

        // Setting wrong URL should give default error code
        $actualErrorCode = $this->getErrorCodeFromCurl('http://doesnotexist.google.com');

        $this->assertEquals(self::DEFAULT_ERRORCODE, $actualErrorCode);

        // Setting correct URLs should give corresponding responses
        $actualErrorCode = $this->getErrorCodeFromCurl($url1);

        $this->assertEquals($expectedErrorCode1, $actualErrorCode);

        $actualErrorCode = $this->getErrorCodeFromCurl($url2);

        $this->assertEquals($expectedErrorCode2, $actualErrorCode);
    }

    public function testGetErrorMessage()
    {
        $curl = $this->curlStub;

        $expectedErrorCode1 = CURLE_UNSUPPORTED_PROTOCOL;
        $expectedErrorMessage1 = 'CURLE_UNSUPPORTED_PROTOCOL';
        $url1 = 'svn://www.google.com';
        $expectedErrorCode2 = CURLE_OK;
        $expectedErrorMessage2 = 'CURLE_OK';
        $url2 = 'http://www.google.com';

        $requiredOptions1 = array(
            CURLOPT_URL => $url1
        );
        $curl->setErrorCode($expectedErrorCode1, $requiredOptions1);
        $requiredOptions2 = array(
            CURLOPT_URL => $url2
        );
        $curl->setErrorCode($expectedErrorCode2, $requiredOptions2);

        // Setting wrong URL should give default error code
        $actualErrorMessage = $this->getErrorMessageFromCurl('http://doesnotexist.google.com');

        $this->assertEquals(self::DEFAULT_ERRORMESSAGE, $actualErrorMessage);

        // Setting correct URLs should give corresponding responses
        $actualErrorMessage = $this->getErrorMessageFromCurl($url1);

        $this->assertEquals($expectedErrorMessage1, $actualErrorMessage);

        $actualErrorMessage = $this->getErrorMessageFromCurl($url2);

        $this->assertEquals($expectedErrorMessage2, $actualErrorMessage);
    }

    public function testSetInformation()
    {
        $curl = $this->curlStub;

        $expectedHttpCode = '404';
        $expectedTotalTime = '2';
        $expectedEffectiveUrl = 'http://www.google.com';

        $explicitInfo = array(
            CURLINFO_HTTP_CODE => $expectedHttpCode,
            CURLINFO_TOTAL_TIME => $expectedTotalTime,
            CURLINFO_EFFECTIVE_URL => $expectedEffectiveUrl
        );
        $url = 'http://www.google.com';

        $requiredOptions = array(
            CURLOPT_URL => $url
        );
        $curl->setInformation($explicitInfo, $requiredOptions);

        $actualHttpCode = $this->getInfoFromCurl($url, CURLINFO_HTTP_CODE);
        $actualTotalTime = $this->getInfoFromCurl($url, CURLINFO_TOTAL_TIME);
        $actualEffectiveUrl = $this->getInfoFromCurl($url, CURLINFO_EFFECTIVE_URL);
        $actualInfoArray = $this->getInfoFromCurl($url);

        $expectedInfoArray = array(
            'http_code' => $expectedHttpCode,
            'total_time' => $expectedTotalTime,
            'url' => $expectedEffectiveUrl
        );

        $this->assertEquals($expectedHttpCode, $actualHttpCode);
        $this->assertEquals($expectedTotalTime, $actualTotalTime);
        $this->assertEquals($expectedEffectiveUrl, $actualEffectiveUrl);

        foreach($expectedInfoArray as $key => $value)
        {
            $this->assertArrayHasKey($key, $actualInfoArray);
            $this->assertEquals($value, $actualInfoArray[$key]);
        }
    }

    public function testDifferentPrioritiesForDifferentOutput()
    {
        $curl = $this->curlStub;

        $expectedResponse1 = 'google page for chrome found';
        $url1 = 'http://www.google.com';
        $userAgent1 = 'Chrome/22.0.1207.1';
        $referer1 = 'http://mail.google.com';
        $expectedErrorCode2 = CURLE_FILESIZE_EXCEEDED;
        $url2 = $url1;
        $userAgent2 = $userAgent1;

        $expectedHttpCode3 = '200';
        $explicitInfo3 = array(
            CURLINFO_HTTP_CODE => $expectedHttpCode3
        );
        $url3 = $url1;

        $requiredOptions1 = array(
            CURLOPT_URL => $url1,
            CURLOPT_USERAGENT => $userAgent1,
            CURLOPT_REFERER => $referer1
        );
        $curl->setResponse($expectedResponse1, $requiredOptions1);
        $requiredOptions2 = array(
            CURLOPT_URL => $url2,
            CURLOPT_USERAGENT => $userAgent2
        );
        $curl->setErrorCode($expectedErrorCode2, $requiredOptions2);
        $requiredOptions3 = array(
            CURLOPT_URL => $url3
        );
        $curl->setInformation($explicitInfo3, $requiredOptions3);

        // most specific case
        $actualResponse = $this->getResponseFromCurl($url1, $requiredOptions1);
        $actualErrorCode = $this->getErrorCodeFromCurl($url1, $requiredOptions1);
        $actualHttpCode = $this->getInfoFromCurl($url1, CURLINFO_HTTP_CODE, $requiredOptions1);

        $this->assertEquals($expectedResponse1, $actualResponse);
        $this->assertEquals($expectedErrorCode2, $actualErrorCode);
        $this->assertEquals($expectedHttpCode3, $actualHttpCode);

        // second-most specific case
        $actualResponse = $this->getResponseFromCurl($url2, $requiredOptions2);
        $actualErrorCode = $this->getErrorCodeFromCurl($url2, $requiredOptions2);
        $actualHttpCode = $this->getInfoFromCurl($url2, CURLINFO_HTTP_CODE, $requiredOptions2);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
        $this->assertEquals($expectedErrorCode2, $actualErrorCode);
        $this->assertEquals($expectedHttpCode3, $actualHttpCode);

        // least specific case
        $actualResponse = $this->getResponseFromCurl($url3, $requiredOptions3);
        $actualErrorCode = $this->getErrorCodeFromCurl($url3, $requiredOptions3);
        $actualHttpCode = $this->getInfoFromCurl($url3, CURLINFO_HTTP_CODE, $requiredOptions3);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
        $this->assertEquals(self::DEFAULT_ERRORCODE, $actualErrorCode);
        $this->assertEquals($expectedHttpCode3, $actualHttpCode);

        // fallback
        $actualResponse = $this->getResponseFromCurl();
        $actualErrorCode = $this->getErrorCodeFromCurl();
        $actualHttpCode = $this->getInfoFromCurl(null , CURLINFO_HTTP_CODE);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
        $this->assertEquals(self::DEFAULT_ERRORCODE, $actualErrorCode);
        $this->assertEquals('', $actualHttpCode);
    }

    private function getResponseFromCurl($url = null, $options = null)
    {
        return $this->getResultFromCurl($url, $options, self::RETURN_RESPONSE);
    }

    private function getErrorCodeFromCurl($url = null, $options = null)
    {
        return $this->getResultFromCurl($url, $options, self::RETURN_ERRORCODE);
    }

    private function getErrorMessageFromCurl($url = null, $options = null)
    {
        return $this->getResultFromCurl($url, $options, self::RETURN_ERRORMESSAGE);
    }

    private function getInfoFromCurl($url = null, $opt = 0, $options = null)
    {
        return $this->getResultFromCurl($url, $options, self::RETURN_INFO, $opt);
    }

    private function getResultFromCurl($url, $options, $returnFlag, $opt = 0)
    {
        $curl = $this->curlStub;
        $ch = $curl->init($url);

        if ($options != null) {
            $curl->setopt_array($ch, $options);
        }

        ob_start();
        $curl->exec($ch);
        $actualResponse = ob_get_clean();

        $result = null;

        switch($returnFlag)
        {
        case self::RETURN_RESPONSE:
            $result = $actualResponse;
            break;
        case self::RETURN_ERRORCODE:
            $result = $curl->errno($ch);
            break;
        case self::RETURN_ERRORMESSAGE:
            $result = $curl->error($ch);
            break;
        case self::RETURN_INFO:
            $result = $curl->getinfo($ch, $opt);
            break;
        }

        $curl->close($ch);

        return $result;
    }
}
