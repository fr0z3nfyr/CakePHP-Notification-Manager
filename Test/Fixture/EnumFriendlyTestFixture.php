<?php

class EnumFriendlyTestFixture extends CakeTestFixture
{
    /**
     * Maps enum fields in the database to strings with a length of 64
     */
    public function create($db)
    {
        foreach ($this->fields as $name => &$field) {
            if (strstr($field['type'], "enum") !== false) {
                $field['type'] = 'string';
                $field['length'] = 64;
            }
        }
        parent::create($db);
    }
}
