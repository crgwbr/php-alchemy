<?php

namespace Alchemy\dialect;

class Compiler {

    protected static $default_tag = 'sql.compile';
    private $aliases = array();
    private $config = array();


    public function __construct($config = array()) {
        $this->pushConfig($config);
    }


    /**
     * Alias global IDs to query-level IDs within a namespace
     *
     * @param  string  $ns namespace
     * @param  string  $id global ID
     * @return integer     query-level alias
     */
    protected function aliasID($ns, $id) {
        if (!isset($this->aliases[$ns])) {
            $this->aliases[$ns] = array('c' => 0);
        }

        $aliases =& $this->aliases[$ns];

        if (!isset($aliases[$id])) {
            $aliases[$id] = $aliases['c']++;
        }

        return $aliases[$id];
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


    /**
     * Apply the special Compiler-format to an array of strings (recursive).
     * Variardic formatting is done with %../<subformat>/<delimiter>/,
     * which applies a format & implode to the remainder of the elements,
     * if any (you may use any punctuation mark in place of '/').
     * <subformat> may contain recursive tokens. It is your responsibility
     * to make sure $format and $subject make sense to use together.
     *
     * Ex: "%s %s (%../+%s/, /)" * [A, B, C, D, E] = "A B (C, D, E)"
     *
     * @param  string $format
     * @param  array  $subject
     * @return string
     */
    public function format($format = '', $subject = '') {
        if (!is_array($subject)) {
            $subject = array($subject);
        }

        if (preg_match("/%\.\.(\p{P})([^\g1]*)\g1([^\g1]*)\g1/", $format, $matches)) {
            list($token, , $subfmt, $delim) = $matches;
            $pos  = strpos($format, $token);
            $skip = $pos ? substr_count($format, '%s', 0, $pos) : 0;
            $tail = array_splice($subject, $skip);

            if ($subfmt) {
                foreach($tail as &$item) {
                    $item = $this->format($subfmt ?: '%s', $item);
                }
            }

            $subject[] = implode($delim, array_filter($tail));
            $format = str_replace($token, '%s', $format);
        }

        return vsprintf($format, $subject);
    }


    public function getConfig($key) {
        $end = end($this->config);
        return isset($end[$key]) ? $end[$key] : null;
    }


    protected function getFunction($obj, $tag = '', $prefix = '') {
        $type = $obj->getTag($tag ?: static::$default_tag);

        if (method_exists($this, "{$prefix}{$type}")) {
            return array($this, "{$prefix}{$type}");
        }

        throw new \Exception("Compiler method not found with prefix '$prefix' for tag '$type' ('$tag')");
    }


    public function popConfig() {
        array_pop($this->config);
    }


    public function pushConfig($config) {
        $end = end($this->config) ?: array();
        array_push($this->config, array_merge($end, $config));
    }


    protected function map($method, $objs) {
        return array_map(array($this, $method), $objs);
    }
 }