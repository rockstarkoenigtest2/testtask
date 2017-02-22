<?php

use Phalcon\Mvc\Model;

class Groups extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->hasMany(
            "id",
            "UsersGroups",
            "groups_id"
        );
    }
}