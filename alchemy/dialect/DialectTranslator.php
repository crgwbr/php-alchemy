<?php

namespace Alchemy\dialect;
use Alchemy\util\Monad;
use Exception;


/**
 * Translate a SQL query into it's frozen, vernacular form. This
 * is how we get around differences in RDBMS query structure and
 * syntax.
 */
class DialectTranslator {
    const FALLBACK_DIALECT = "ANSI";

    protected $dialect;
    protected $namespace;

    /**
     * Object constructor.
     *
     * @param string $dialect Which SQL dialect to translate into to
     * @param string $namespace Namespace of dialect classes
     */
    public function __construct($dialect, $namespace = "Alchemy\\dialect") {
        $this->dialect = $dialect;
        $this->namespace = $namespace;
    }


    /**
     * Get the name of the vernacular class which corresponds to the
     * given class and dialect.
     *
     * @param string $master Class Name
     * @param string $dialect
     * @return string Vernacular Class Name
     */
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


    /**
     * Translate a query into vernacular form
     *
     * @param Query $sqlexpr Query, optionally wrapped in a Monad
     * @return ANSI_Query
     */
    public function translate($sqlexpr) {
        // Get rid of the monad wrapper
        if ($sqlexpr instanceof Monad) {
            $sqlexpr = $sqlexpr->unwrap();
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
