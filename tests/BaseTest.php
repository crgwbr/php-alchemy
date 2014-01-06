<?php

namespace Alchemy\tests;
require_once "alchemy/alchemy.php";

use Alchemy\engine\Engine;


date_default_timezone_set('UTC');
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

// Update include path so that the following includes work
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

require_once 'resources/Language.php';


abstract class BaseTest extends \PHPUnit_Framework_TestCase {

    protected $fnCallback;

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


    /**
     * Assert that a callable throws an exception of a particular type when called
     */
    protected function assertThrows($exception, $fnSubject) {
        $args = func_get_args();
        array_shift($args);
        array_shift($args);

        try {
            call_user_func_array($fnSubject, $args);
        } catch(\Exception $e) {
            return is_string($exception)
                ? $this->assertInstanceOf($exception, $e)
                : $this->assertSame($exception, $e);
        }

        return $this->fail("{$exception} was not thrown.");
    }


    /**
     * Use this method to start a mock expects() builder, then pass
     * $this->fnCallback to the function you want to test
     */
    protected function expectsCallback($times) {
        $this->fnCallback = $this->getMock('stdClass', array('__invoke'));
        return $this->fnCallback->expects($times)->method('__invoke');
    }
}
