<?php

require_once "alchemy/alchemy.php";

date_default_timezone_set('UTC');


abstract class BaseTest extends PHPUnit_Framework_TestCase {

    public function assertExpectedString($file, $str) {
        $file = dirname(__FILE__)
              . DIRECTORY_SEPARATOR
              . "expected"
              . DIRECTORY_SEPARATOR
              . $file;
        return $this->assertStringEqualsFile($file, $str);
    }
}
