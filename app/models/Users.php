<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Db\Column;
\Phalcon\Mvc\Model::setup(['castOnHydrate' => true]);

class Users extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $email;


    public function fixTypes() 
    {
        foreach ($this->getModelsMetaData()->getDataTypes($this) as $field => $type) 
        {
            if (is_null($this->$field)) 
            {
                continue;
            }
            switch ($type) 
            {
                case Column::TYPE_BOOLEAN:
                    $this->$field = ord($this->$field);
                    break;
                case Column::TYPE_BIGINTEGER:
                case Column::TYPE_INTEGER:
                    $this->$field = intval($this->$field);
                    break;
                case Column::TYPE_DECIMAL:
                case Column::TYPE_FLOAT:
                    $this->$field = floatval($this->$field);
                    break;
                case Column::TYPE_DOUBLE:
                    $this->$field = doubleval($this->$field);
                    break;
            }
        }
    }


    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cubicfox");
        $this->setSource("users");

        $this->hasMany("id", "Products", "user_id");

        $this->hasMany("id", "Rates", "user_id");

    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]|Users|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
