<?php

namespace Alchemy\tests;
require_once "alchemy/alchemy.php";

use Alchemy\engine\Engine;


date_default_timezone_set('UTC');
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

// Update include path so that the following includes work
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));


abstract class BaseTest extends \PHPUnit_Framework_TestCase {

    public function assertExpectedString($file, $str) {
        $file = dirname(__FILE__)
              . DIRECTORY_SEPARATOR
              . "expected"
              . DIRECTORY_SEPARATOR
              . $file;
        return $this->assertStringEqualsFile($file, $str);
    }


    protected function getMySQLEngine() {
        return new Engine("mysql:dbname=myapp_test;host=127.0.0.1", 'travis');
    }


    protected function getSQLiteEngine() {
        return new Engine('sqlite::memory:');
    }
}
