<?php

namespace Alchemy\dialect;

class Compiler {

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


    protected function getConfig($key) {
        $end = end($this->config);
        return isset($end[$key]) ? $end[$key] : null;
    }


    protected function getFunction($obj, $prefix = '', $strict = false) {
        foreach($obj->listRoles() as $role) {
            if (method_exists($this, "{$prefix}{$role}")) {
                return array($this, "{$prefix}{$role}");
            }

            if ($strict) break;
        }

        $roles = array_slice($obj->listRoles(), 0, $strict ? 1 : -1);
        $roles = implode(', ', $roles);
        throw new \Exception("Compiler method not found with prefix '$prefix' for roles [$roles]");
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