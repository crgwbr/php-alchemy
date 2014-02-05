<?php

namespace Alchemy\core\schema;


/**
 * Represent a column in SQL with a foreign key constraint to another column
 */
class Foreign extends Column {
    public function copy(array $args = array(), $table = null, $name = '?') {
        $source = is_string($this->args[0])
            ? Column::find($this->args[0], $table)
            : $this->args[0];

        // only override keyword args
        $args['foreign_key'] = $source;
        $args['auto_increment'] = false;
        $args += array_slice($this->args, 1, NULL);

        return $source->copy($args, $table, $name);
    }
}