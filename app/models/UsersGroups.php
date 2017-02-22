<?php

use Phalcon\Mvc\Model;

class UsersGroups extends Model
{
    public $id;

    public function initialize()
    {
        $this->belongsTo(
            "users_id",
            "Users",
            "id"
        );

        $this->belongsTo(
            "groups_id",
            "Groups",
            "id"
        );
    }
}