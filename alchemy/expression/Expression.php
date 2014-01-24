<?php

namespace Alchemy\expression;


/**
 * Abstract class for composing expressions
 */
abstract class Expression extends Element {
    // Expression [type => arity] map
    protected static $types = array();

    // take this tag, result in that tag
    protected static $element_tag = null;
    protected static $return_tag = null;

    // map of static Expression types to subclasses
    protected static $static_ops = array(
        'and'      => 'CompoundPredicate',
        'or'       => 'CompoundPredicate',
        'not'      => 'NegationPredicate',
        //'if'       => 'ConditionalExpression',
        //'case'     => 'CaseExpression',
        'coalesce' => 'Operation',
        'interval' => 'Operation',
        'now'      => 'Operation');

    protected $elements = array();
    protected $type;


    public static function __callStatic($name, $args) {
        $type = trim(strtolower($name), '_');
        if (array_key_exists($type, self::$static_ops)) {
            $cls = __NAMESPACE__ . '\\' . self::$static_ops[$type];
            return new $cls($type, $args);
        }
    }


    public function __construct($type, array $elements) {
        $cls = get_called_class();

        if (!array_key_exists($type, static::$types)) {
            throw new \Exception("Unknown {$cls} '{$type}'");
        }

        $def = static::$types[$type];
        $tag = static::$element_tag;

        if ($def != -1 && $def != count($elements)) {
            throw new \Exception("{$cls} '{$type}': Expected {$def} elements, got " . count($elements));
        }

        foreach ($elements as $element) {
            if (!$element->getTag($tag)) {
                throw new \Exception("{$cls} '{$type}': Expected '{$tag}' at position {$key}");
            }
        }

        $this->elements = $elements;
        $this->type = $type;
        $this->addTag("sql.compile", "Expression");
        $this->addTag(static::$result_tag);
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


    public function getType() {
        return $this->type;
    }
}
