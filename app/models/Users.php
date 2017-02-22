<?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class Users extends Model
{
    public $id;
    public $name;
    public $email;
    public $password;

    public function initialize()
    {
        $this->hasMany(
            "id",
            "UsersGroups",
            "users_id", array('alias' => 'UsersGroups')
        );

        $this->hasManyToMany(
            "id", "UsersGroups", "users_id",
            "groups_id", "Groups", "id", array('alias' => 'groups'));
    }

    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            'email',
            new EmailValidator([
                'model' => $this,
                'message' => 'Please enter a correct email address.'
            ])
        );

        $validator->add(
            'email',
            new UniquenessValidator([
                'model' => $this,
                'message' => 'Sorry, that email is already taken.',
            ])
        );

        return $this->validate($validator);
    }

    public function __set($property, $value)
    {
        if ($value instanceof \Phalcon\Mvc\Model\ResultSetInterface)
        {
            $value = $value->filter(function($r) {
                return $r;
            });
        }
        parent::__set($property, $value);
    }
}