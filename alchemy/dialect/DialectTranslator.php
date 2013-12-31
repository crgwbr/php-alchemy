<?php

namespace Alchemy\dialect;
use Alchemy\expression\QueryManager;
use Exception;


class DialectTranslator {
    const FALLBACK_DIALECT = "ANSI";

    protected $dialect;
    protected $namespace;

    public function __construct($dialect, $namespace = "Alchemy\\dialect") {
        $this->dialect = $dialect;
        $this->namespace = $namespace;
    }


    protected function getVernacularClassName($master, $dialect) {
        // String namespace from class name
        $cls = explode("\\", $master);
        $cls = array_pop($cls);

        // Try Given dialect
        $vern = "{$this->namespace}\\{$dialect}_{$cls}";
        if (class_exists($vern)) {
            return $vern;
        }

        // Try ANSI
        $vern = "{$this->namespace}\\" . self::FALLBACK_DIALECT . "_{$cls}";
        if (class_exists($vern)) {
            return $vern;
        }

        // Traverse up inheritance tree
        $parent = get_parent_class($master);
        if ($parent) {
            return $this->getVernacularClassName($parent, $dialect);
        }

       // Die
       throw new Exception("{$dialect} vernacular class does not exists for {$master}");
    }


    public function translate($sqlexpr) {
        // Get rid of the monad wrapper
        if ($sqlexpr instanceof QueryManager) {
            $sqlexpr = $sqlexpr->getQuery();
        }

        // Array?
        if (is_array($sqlexpr)) {
            return array_map(array($this, 'translate'), $sqlexpr);
        }

        // Scalar?
        if (!is_object($sqlexpr)) {
            return $sqlexpr;
        }

        // Convert object data into an array
        $data = array();
        foreach ((array)$sqlexpr as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $value = $this->translate($value);
            }

            $key = trim($key, "\0* ");
            $data[$key] = $value;
        }

        // Create a vernacular version of the orign object
        $cls = get_class($sqlexpr);
        $cls = $this->getVernacularClassName($cls, $this->dialect);
        $vernacular = new $cls($data);

        return $vernacular;
    }
}
