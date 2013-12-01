<?php
namespace FACTFinder\Test\Util;

use FACTFinder\Loader as FF;

/**
 * Tests the parts of CurlStub that correspond to cURL's multi interface.
 */
class CurlStubMultiTest extends \FACTFinder\Test\BaseTestCase
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

    public function testAddAndRemoveHandles()
    {
        $curl = $this->curlStub;

        $ch = $curl->init();
        $mh = $curl->multi_init();

        // Try invalid multi handle
        $this->assertEquals(CURLM_BAD_HANDLE, $curl->multi_add_handle($mh+1, $ch));
        // Try invalid easy handle
        $this->assertEquals(CURLM_BAD_EASY_HANDLE, $curl->multi_add_handle($mh, $ch+1));

        $this->assertEquals(CURLM_OK, $curl->multi_add_handle($mh, $ch));

        // Try adding again, the exact error code depends on the cURL version
        $this->assertContains(
            $curl->multi_add_handle($mh, $ch),
            array(
                CURLM_BAD_EASY_HANDLE,
                $curl::M_ADDED_ALREADY
            )
        );

        // This shouldn't do anything
        $curl->close($ch);
        // really!
        $curl->close($ch);

        $curl->multi_remove_handle($mh, $ch);

        $curl->close($ch);

        $curl->close($mh);
    }

    public function testSetResponse()
    {
        $curl = $this->curlStub;
        $ch = $curl->init();

        $mh = $curl->multi_init();

        $curl->multi_add_handle($mh, $ch);

        ob_start();

        do
        {
            $status = $curl->multi_exec($mh, $still_running);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $status == CURLM_OK)
        {
            if ($curl->multi_select($mh) == -1)
                usleep(100);

            do
            {
                $status = $curl->multi_exec($mh, $still_running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);
        }

        if ($status != CURLM_OK)
            $this->assertFalse(true, 'There was a cURL error: ' . $status);

        $msg = $curl->multi_info_read($mh);
        $this->assertTrue(is_array($msg));
        $this->assertEquals(CURLMSG_DONE, $msg['msg']);
        $this->assertEquals(self::DEFAULT_ERRORCODE, $msg['result']);
        $this->assertEquals($ch, $msg['handle']);

        $actualResponse = ob_get_clean();

        $curl->multi_remove_handle($mh, $ch);
        $curl->close($ch);

        $curl->multi_close($mh);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
    }

    public function testGetContent()
    {
        $curl = $this->curlStub;
        $ch = $curl->init();
        $curl->setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $mh = $curl->multi_init();

        $curl->multi_add_handle($mh, $ch);

        do
        {
            $status = $curl->multi_exec($mh, $still_running);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $status == CURLM_OK)
        {
            if ($curl->multi_select($mh) == -1)
                usleep(100);

            do
            {
                $status = $curl->multi_exec($mh, $still_running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);
        }

        if ($status != CURLM_OK)
            $this->assertFalse(true, 'There was a cURL error: ' . $status);

        $msg = $curl->multi_info_read($mh);
        $this->assertTrue(is_array($msg));
        $this->assertEquals(CURLMSG_DONE, $msg['msg']);
        $this->assertEquals(self::DEFAULT_ERRORCODE, $msg['result']);
        $this->assertEquals($ch, $msg['handle']);

        $actualResponse = $curl->multi_getcontent($ch);

        $curl->multi_remove_handle($mh, $ch);
        $curl->close($ch);

        $curl->multi_close($mh);

        $this->assertEquals(self::DEFAULT_RESPONSE, $actualResponse);
    }

    public function testMultipleHandles()
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

        $ch = array();

        // Setting wrong URL should give default response
        $ch[0] = $curl->init('http://www.yahoo.com');
        $curl->setopt($ch[0], CURLOPT_RETURNTRANSFER, true);
        // Setting correct URLs should give corresponding responses
        $ch[1] = $curl->init($url1);
        $curl->setopt($ch[1], CURLOPT_RETURNTRANSFER, true);
        $ch[2] = $curl->init($url2);
        $curl->setopt($ch[2], CURLOPT_RETURNTRANSFER, true);

        $mh = $curl->multi_init();

        $curl->multi_add_handle($mh, $ch[0]);
        $curl->multi_add_handle($mh, $ch[1]);
        $curl->multi_add_handle($mh, $ch[2]);

        do
        {
            $status = $curl->multi_exec($mh, $still_running);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $status == CURLM_OK)
        {
            if ($curl->multi_select($mh) == -1)
                usleep(100);

            do
            {
                $status = $curl->multi_exec($mh, $still_running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);
        }

        if ($status != CURLM_OK)
            $this->assertFalse(true, 'There was a cURL error: ' . $status);

        for ($i = 0; $i < 3; ++$i)
        {
            $msg = $curl->multi_info_read($mh);
            $this->assertTrue(is_array($msg));
            $this->assertEquals(CURLMSG_DONE, $msg['msg']);
            $this->assertEquals(self::DEFAULT_ERRORCODE, $msg['result']);
            $handle = $msg['handle'];

            $this->assertTrue(in_array($handle, $ch));
        }

        $this->assertEquals(self::DEFAULT_RESPONSE,
                            $curl->multi_getcontent($ch[0]));
        $this->assertEquals($expectedResponse1,
                            $curl->multi_getcontent($ch[1]));
        $this->assertEquals($expectedResponse2,
                            $curl->multi_getcontent($ch[2]));

        $curl->multi_remove_handle($mh, $ch[0]);
        $curl->multi_remove_handle($mh, $ch[1]);
        $curl->multi_remove_handle($mh, $ch[2]);

        $curl->close($ch[0]);
        $curl->close($ch[1]);
        $curl->close($ch[2]);

        $curl->multi_close($mh);
    }

    public function testMultipleHandlesDuringLoop()
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

        $ch = array();

        // Setting wrong URL should give default response
        $ch[0] = $curl->init('http://www.yahoo.com');
        $curl->setopt($ch[0], CURLOPT_RETURNTRANSFER, true);
        // Setting correct URLs should give corresponding responses
        $ch[1] = $curl->init($url1);
        $curl->setopt($ch[1], CURLOPT_RETURNTRANSFER, true);
        $ch[2] = $curl->init($url2);
        $curl->setopt($ch[2], CURLOPT_RETURNTRANSFER, true);

        $mh = $curl->multi_init();

        $curl->multi_add_handle($mh, $ch[0]);
        $curl->multi_add_handle($mh, $ch[1]);
        $curl->multi_add_handle($mh, $ch[2]);

        do
        {
            $status = $curl->multi_exec($mh, $still_running);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        $responseObtained = array(false, false, false);

        while ($still_running && $status == CURLM_OK)
        {
            if ($curl->multi_select($mh) == -1)
                usleep(100);

            do
            {
                $status = $curl->multi_exec($mh, $still_running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);

            while ($msg = $curl->multi_info_read($mh))
            {
                $this->assertTrue(is_array($msg));
                $this->assertEquals(self::DEFAULT_ERRORCODE, $msg['result']);

                // In production code curl_multi_getcontent() should only be
                // called if $msg['result'] is CURLE_OK. However, the CurlStub
                // allows for responses to be set even for error codes that
                // indicate unsuccessful connections.

                $handle = $msg['handle'];
                $this->assertTrue(in_array($handle, $ch));

                $actualResponse = $curl->multi_getcontent($handle);
                switch ($handle)
                {
                case $ch[0]:
                    $this->assertFalse($responseObtained[0], "Response already fetched.");
                    $this->assertEquals(self::DEFAULT_RESPONSE,
                                        $actualResponse);
                    $responseObtained[0] = true;
                    break;
                case $ch[1]:
                    $this->assertFalse($responseObtained[1], "Response already fetched.");
                    $this->assertEquals($expectedResponse1,
                                        $actualResponse);
                    $responseObtained[1] = true;
                    break;
                case $ch[2]:
                    $this->assertFalse($responseObtained[2], "Response already fetched.");
                    $this->assertEquals($expectedResponse2,
                                        $actualResponse);
                    $responseObtained[2] = true;
                    break;
                }
            }
        }

        if ($status != CURLM_OK)
            $this->assertFalse(true, 'There was a cURL error: ' . $status);

        $this->assertFalse(in_array(false, $responseObtained));

        $curl->multi_remove_handle($mh, $ch[0]);
        $curl->multi_remove_handle($mh, $ch[1]);
        $curl->multi_remove_handle($mh, $ch[2]);

        $curl->close($ch[0]);
        $curl->close($ch[1]);
        $curl->close($ch[2]);

        $curl->multi_close($mh);
    }

    public function testMultipleHandlesWithDifferentOptions()
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

        $tooSpecificOptions = array(
            CURLOPT_URL => $url1,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64)"
        );

        $unmatchedOptions = array(
            CURLOPT_REFERER => $referer4
        );

        $ch = array();

        // Setting wrong URL should give default response
        $ch[0] = $curl->init('http://www.yahoo.com');
        $curl->setopt($ch[0], CURLOPT_RETURNTRANSFER, true);
        $ch[1] = $curl->init($url1);
        $curl->setopt($ch[1], CURLOPT_RETURNTRANSFER, true);
        $ch[2] = $curl->init($url2);
        $curl->setopt($ch[2], CURLOPT_RETURNTRANSFER, true);
        $curl->setopt_array($ch[2], $requiredOptions2);
        $ch[3] = $curl->init($url3);
        $curl->setopt($ch[3], CURLOPT_RETURNTRANSFER, true);
        $curl->setopt_array($ch[3], $requiredOptions3);
        $ch[4] = $curl->init($url4);
        $curl->setopt($ch[4], CURLOPT_RETURNTRANSFER, true);
        $curl->setopt_array($ch[4], $requiredOptions4);
        $ch[5] = $curl->init($url1);
        $curl->setopt($ch[5], CURLOPT_RETURNTRANSFER, true);
        $curl->setopt_array($ch[5], $tooSpecificOptions);
        $ch[6] = $curl->init();
        $curl->setopt($ch[6], CURLOPT_RETURNTRANSFER, true);
        $curl->setopt_array($ch[6], $unmatchedOptions);
        $ch[7] = $curl->init('http://www.yahoo.com');
        $curl->setopt($ch[7], CURLOPT_RETURNTRANSFER, true);
        $curl->setopt_array($ch[7], $unmatchedOptions);

        $mh = $curl->multi_init();

        $curl->multi_add_handle($mh, $ch[0]);
        $curl->multi_add_handle($mh, $ch[1]);
        $curl->multi_add_handle($mh, $ch[2]);
        $curl->multi_add_handle($mh, $ch[3]);
        $curl->multi_add_handle($mh, $ch[4]);
        $curl->multi_add_handle($mh, $ch[5]);
        $curl->multi_add_handle($mh, $ch[6]);
        $curl->multi_add_handle($mh, $ch[7]);

        do
        {
            $status = $curl->multi_exec($mh, $still_running);
        } while ($status == CURLM_CALL_MULTI_PERFORM);

        while ($still_running && $status == CURLM_OK)
        {
            if ($curl->multi_select($mh) == -1)
                usleep(100);

            do
            {
                $status = $curl->multi_exec($mh, $still_running);
            } while ($status == CURLM_CALL_MULTI_PERFORM);
        }

        if ($status != CURLM_OK)
            $this->assertFalse(true, 'There was a cURL error: ' . $status);

        for ($i = 0; $i < 8; ++$i)
        {
            $msg = $curl->multi_info_read($mh);
            $this->assertTrue(is_array($msg));
            $this->assertEquals(CURLMSG_DONE, $msg['msg']);
            $this->assertEquals(self::DEFAULT_ERRORCODE, $msg['result']);
            $handle = $msg['handle'];

            $this->assertTrue(in_array($handle, $ch));
        }

        $this->assertEquals(self::DEFAULT_RESPONSE,
                            $curl->multi_getcontent($ch[0]));
        $this->assertEquals($expectedResponse1,
                            $curl->multi_getcontent($ch[1]));
        $this->assertEquals($expectedResponse2,
                            $curl->multi_getcontent($ch[2]));
        $this->assertEquals($expectedResponse3,
                            $curl->multi_getcontent($ch[3]));
        $this->assertEquals($expectedResponse4,
                            $curl->multi_getcontent($ch[4]));
        $this->assertEquals($expectedResponse1,
                            $curl->multi_getcontent($ch[5]));
        $this->assertEquals(self::DEFAULT_RESPONSE,
                            $curl->multi_getcontent($ch[6]));
        $this->assertEquals(self::DEFAULT_RESPONSE,
                            $curl->multi_getcontent($ch[7]));

        $curl->multi_remove_handle($mh, $ch[0]);
        $curl->multi_remove_handle($mh, $ch[1]);
        $curl->multi_remove_handle($mh, $ch[2]);
        $curl->multi_remove_handle($mh, $ch[3]);
        $curl->multi_remove_handle($mh, $ch[4]);
        $curl->multi_remove_handle($mh, $ch[5]);
        $curl->multi_remove_handle($mh, $ch[6]);
        $curl->multi_remove_handle($mh, $ch[7]);

        $curl->close($ch[0]);
        $curl->close($ch[1]);
        $curl->close($ch[2]);
        $curl->close($ch[3]);
        $curl->close($ch[4]);
        $curl->close($ch[5]);
        $curl->close($ch[6]);
        $curl->close($ch[7]);

        $curl->multi_close($mh);
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
