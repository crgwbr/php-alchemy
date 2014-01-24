<?php

namespace Alchemy\expression;


/**
 * Base class for composable elements
 */
class Element implements IElement {

    protected static $id_counter = 0;

    protected $id;
    protected $tags = array();


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


    /**
     * List of all tags that apply to this element
     *
     * @return array
     */
    public function listTags() {
        return array_keys(array_filter($this->tags));
    }
}
