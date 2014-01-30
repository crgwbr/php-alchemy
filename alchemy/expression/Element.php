<?php

namespace Alchemy\expression;


/**
 * Base class for composable elements
 */
class Element implements IElement {

    private static $id_counter = 0;
    private static $typedefs = array();

    protected $id;
    protected $type;
    protected $tags = array();


    public static function define($type = null, $base = null, $def = array()) {
        $parts = explode('\\', get_called_class());
        $class = array_pop($parts);

        $type = $type ?: $class;
        $base = $base ?: $class;

        // get base definition
        $basedef = ($base != $type)
            ? self::get_definition($base)
            : array();
        $basedef['tags']["element.type"] = $type;
        $basedef['tags']["element.class"] = $class;

        // merge with new defition (non-recursive)
        foreach ($basedef as $k => $v) {
            $def[$k] = array_key_exists($k, $def)
                ? (is_array($v) ? $def[$k] + $v : $def[$k])
                : $v;
        }

        self::$typedefs["{$class}.{$type}"] = $def;
    }


    public static function get_definition($type) {
        $parts = explode('\\', get_called_class());
        $class = array_pop($parts);

        if (!array_key_exists("{$class}.{$type}", self::$typedefs)) {
            throw new \Exception("No Element definition for {$class}.{$type}");
        }

        return self::$typedefs["{$class}.{$type}"];
    }


    public function __construct($type = null) {
        if ($type) {
            $def = self::get_definition($type);
            $this->addTags($def['tags']);
        }
        $this->type = $type;
    }


    /**
     * Apply a tag to this element. The same tag cannot be applied
     * with two different values.
     *
     * @param string $tag
     * @param string $value optional value to give tag
     */
    public function addTag($tag, $value = true) {
        if (array_key_exists($tag, $this->tags) && $this->tags[$tag] !== true) {
            if ($value === true) {
                return;
            } elseif ($value !== $this->tags[$tag]) {
                throw new \Exception("Tag '{$tag}' already has value {$this->tags[$tag]}, cannot reapply with value '{$value}'");
            }
        }

        $this->tags[$tag] = $value;
    }


    public function addTags($tags) {
        foreach ($tags as $tag => $value) {
            $this->addTag($tag, $value);
        }
    }


    /**
     * Get the locally-unique element id
     *
     * @return string
     */
    public function getID() {
        if (!$this->id) {
            $this->id = ++self::$id_counter;
        }

        return $this->id;
    }


    /**
     * If the tag has been applied to this object, returns its
     * value, else false
     *
     * @param  string $tag tag name
     * @return mixed       value or false
     */
    public function getTag($tag) {
        if ($tag && array_key_exists($tag, $this->tags)) {
            return $this->tags[$tag];
        }

        return false;
    }


    public function getType() {
        return $this->type;
    }


    /**
     * List of all tags that apply to this element
     *
     * @return array
     */
    public function listTags() {
        return array_keys(array_filter($this->tags));
    }
}
