<?php

namespace Alchemy\dialect;

class Compiler {

    protected static $default_tag = 'sql.compile';

    private $config = array();

    protected $defaults = array();


    public function __construct($config = array()) {
        $this->pushConfig($config + $this->defaults);
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
            $fn = $this->getFunction($obj, static::$default_tag, '', true);
            $result = call_user_func($fn, $obj);
        }

        if ($config) {
            $this->popConfig();
        }

        return $result;
    }


    /**
     * Apply the special Compiler-format to an array of strings (recursive).
     * Variardic formatting is done with %/<subformat>/<delimiter>/,
     * which applies a format & implode to the remainder of the elements,
     * if any (you may use any punctuation mark in place of '/').
     * <subformat> may contain recursive tokens. It is your responsibility
     * to make sure $format and $subject make sense to use together.
     *
     * Ex: "%s %s (%4/+%s/, /)" * [A, B, C, D, E] = "A B (D, E)"
     * Ex: "%2$s (%2$1/+%s/, /)" * [[A, B, C], D] = "D (A, B, C)"
     *
     * @param  string $format
     * @param  array  $subject
     * @return string
     */
    public function format($format = '', $subject = '') {
        if (!is_array($subject)) {
            $subject = array($subject);
        }

        while (preg_match("/.*?(%(?:(\d+).)?(\d+)?(\p{P})([^\g4]*?)\g4([^\g4]*?)\g4)/", $format, $matches)) {
            list(,$token, $pos, $start, $p, $subfmt, $delim) = $matches;
            $start = ((int) $start ?: 1) - 1;
            $pos   = ((int) $pos   ?: 1) - 1;
            $tail = array_slice($pos ? $subject[$pos] : $subject, $start);

            if ($subfmt) {
                foreach($tail as &$item) {
                    $item = $this->format($subfmt ?: '%s', $item);
                }
            }

            $subject[] = implode($delim, array_filter($tail));
            $format = str_replace($token, '%'.count($subject).'$s', $format);
        }

        return vsprintf($format, $subject);
    }


    public function getConfig($key) {
        $end = end($this->config);
        return isset($end[$key]) ? $end[$key] : null;
    }


    protected function getFunction($obj, $tag = '', $prefix = '', $strict = false) {
        $type = $obj->getTag($tag ?: static::$default_tag);

        if (method_exists($this, "{$prefix}{$type}")) {
            return array($this, "{$prefix}{$type}");
        }

        if ($strict) {
            throw new \Exception("Compiler method not found with prefix '$prefix' for '$tag' = '$type'");
        }
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