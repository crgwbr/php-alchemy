<?php

namespace Alchemy\dialect;

class Compiler {

    private $config = array();


    public function __construct($config = array()) {
        $this->pushConfig($config);
    }


    /**
     * Compile an Expression or array of Expressions into a string.
     *
     * @param  array|Expression $obj    object(s) to compile
     * @param  array            $config temporary config overrides
     * @return array|string             compiled result(s)
     */
    public function compile($obj = null, $config = null) {
        $result = '';

        if ($config) {
            $this->pushConfig($config);
        }

        if (is_array($obj)) {
            $result = $this->map('compile', $obj);
        } elseif (is_object($obj)) {
            $fn = $this->getFunction($obj);
            $result = call_user_func($fn, $obj);
        }

        if ($config) {
            $this->popConfig();
        }

        return $result;
    }


    protected function getConfig($key) {
        $end = end($this->config);
        return isset($end[$key]) ? $end[$key] : null;
    }


    protected function getFunction($obj, $prefix = '', $strict = false) {
        $cls = get_class($obj);

        do {
            $short = substr(strrchr($cls, '\\'), 1);
            if (method_exists($this, "{$prefix}{$short}")) {
                return array($this, "{$prefix}{$short}");
            }
            $cls = get_parent_class($cls);
        } while ($cls && !$strict);

        throw new \Exception("Compiler method not found for " . get_class($obj));
    }


    protected function popConfig() {
        array_pop($this->config);
    }


    protected function pushConfig($config) {
        $end = end($this->config) ?: array();
        array_push($this->config, array_merge($end, $config));
    }


    protected function map($method, $objs) {
        return array_map(array($this, $method), $objs);
    }
 }