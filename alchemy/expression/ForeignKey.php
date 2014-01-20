<?php

namespace Alchemy\expression;
use Alchemy\expression\Column;


/**
 * Represent a column in SQL with a foreign key constraint to another column
 */
class ForeignKey extends Column {
    public function copy(array $args = array(), $table = null, $name = '?') {
        $source = $this->args[0];

        if (is_string($source)) {
            list($ref, $col) = explode('.', $source);

            $reftable = ($ref == 'self') ? $table : Table::find($ref);
            if (!($reftable instanceof Table)) {
                throw new \Exception("Cannot find Table '{$ref}'.");
            }

            $source = $reftable->{$col};
        }

        // use source positional args, but our keyword args
        $args += array($source->getArg(0), 'foreign_key' => $source) + $this->args;

        return $source->copy($args, $table, $name);
    }
}