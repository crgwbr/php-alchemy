<?php
/**
 * Parse a data type definition into it's various components.
 * For example:
 *     >>> $type = new DataTypeLexer("Integer(11, primary_key = true)");
 *     >>> $type->getType();
 *     "Integer"
 *     >>> $type->getArgs();
 *     array(11)
 *     >>> $type->getKeywordArgs();
 *     array("primary_key" => true)
 */
namespace  Alchemy\orm;


class DataTypeLexer {
    const T_EQUALS = '=';

    private $index = 0;
    private $definition;
    private $type;
    private $args;
    private $kwargs;


    public function __construct($def) {
        $this->definition = $def;
        $this->parse($def);
    }


    public function getArgs() {
        return $this->args;
    }


    public function getKeywordArgs() {
        return $this->kwargs;
    }


    public function getType() {
        return $this->type;
    }


    protected function lexString($def) {
        $def = str_split($def);
        $tokens = array();
        $inString = false;
        $buffer = "";

        while (count($def) > 0) {
            $char = array_shift($def);

            // Start / Stop String
            if (preg_match("/[\"\']/", $char)) {
                $inString = !$inString;
                continue;
            }

            // Escape character?
            if ($char == "\\") {
                $buffer .= array_shift($def);
                continue;
            }

            // Push value onto buffer
            if ($inString || preg_match("/[a-zA-Z0-9_\-]/", $char)) {
                $buffer .= $char;
                continue;
            }

            if (strlen($char) == 0 || $char === ' ') {
                continue;
            }

            // Reached a control char. Record the token and reset the buffer
            if (strlen($buffer) > 0) {
                $tokens[] = $this->normalizeToken($buffer);
                $buffer = "";
            }

            $tokens[] = $char;
        }

        // Save last buffer
        if (strlen($buffer) > 0) {
            $tokens[] = $this->normalizeToken($buffer);
        }

        return $tokens;
    }


    protected function normalizeToken($token) {
        switch (true) {
            case $token === 'true':
                return true;

            case $token === 'false':
                return false;

            case (string)(int)$token == $token:
                return (int)$token;

            case is_numeric($token):
                return (float)$token;
        }

        return $token;
    }


    protected function parse($def) {
        $tokens = $this->lexString($def);
        $this->type = array_shift($tokens);
        $this->args = array();
        $this->kwargs = array();

        while ($token = array_shift($tokens)) {
            // Ignore controls
            if (preg_match("/[\(\)\,]/", $token)) {
                continue;
            }

            if (reset($tokens) == static::T_EQUALS) {
                array_shift($tokens); // Swallow the assignment
                $value = array_shift($tokens);
                $this->kwargs[$token] = $value;
            } else {
                $this->args[] = $token;
            }
        }
    }
}