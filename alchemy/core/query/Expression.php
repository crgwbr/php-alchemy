<?php

namespace Alchemy\core\query;
use Alchemy\core\Element;


/**
 * Class for composing expressions
 */
class Expression extends Element {

    protected $elements = array();


    public static function __callStatic($name, $args) {
        $def = self::get_definition(trim(strtolower($name), '_'));
        $elements = (isset($args[0]) && is_array($args[0])) ? $args[0] : $args;

        return new $def['tags']['element.class']($def['tags']['element.type'], $elements);
    }


    public function __construct($type, array $elements) {
        parent::__construct($type);
        $cls = get_called_class();

        $def = self::get_definition($type);
        $tag = $def['tags']['expr.element'];

        if ($def['arity'] != -1 && $def['arity'] != count($elements)) {
            throw new \Exception("{$cls} '{$type}': Expected {$def['arity']} elements, got " . count($elements));
        }

        foreach ($elements as $element) {
            if (!$element->getTag($tag)) {
                throw new \Exception("{$cls} '{$type}': Expected '{$tag}' at position {$key}");
            }
        }

        $this->elements = $elements;
    }


    public function getElements() {
        return $this->elements;
    }


    /**
     * Recursively get all scalar parameters used by this expression
     *
     * @return array array(Scalar, Scalar, ...)
     */
    public function getParameters() {
        $params = array();

        foreach ($this->elements as $element) {
            if (method_exists($element, 'getParameters')) {
                $params = array_merge($params, $element->getParameters());
            }
        }

        return $params;
    }
}
