<?php
namespace FACTFinder\Util;

/**
 * Interface for PHP cURL functions.
 **/
interface CurlInterface
{
    public function close($ch);
    public function copy_handle($ch);
    public function errno($ch);
    public function error($ch);
    public function exec($ch);
    public function getinfo($ch, $opt = 0);
    public function init($url = null);
    public function multi_add_handle($mh, $ch);
    public function multi_close($mh);
    public function multi_exec($mh, &$still_running);
    public function multi_getcontent($ch);
    public function multi_info_read($mh, &$msgs_in_queue = null);
    public function multi_init();
    public function multi_remove_handle($mh, $ch);
    public function multi_select($mh, $timeout = 1.0);
    public function setopt_array($ch, $options);
    public function setopt($ch, $option, $value);
    public function version($age = CURLVERSION_NOW);
}
