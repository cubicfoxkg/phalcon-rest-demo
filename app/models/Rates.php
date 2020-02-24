<?php
\Phalcon\Mvc\Model::setup(['castOnHydrate' => true]);
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Alnum as AlnumValidator;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Numericality as NumericalityValidator;


class Rates extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $user_id;

    /**
     *
     * @var integer
     */
    public $product_id;

    /**
     *
     * @var string
     */
    public $rating;

    //Rating validation, dont need if we use Phalcons validation
    //1-10
    /*
    public function setRating(float $rating): Rates
    {
        if (($rating < 1) || ($rating > 10)) 
        {
            throw new Exception('Invalid rating, only 1-10', 400);        
        }

        $this->rating = $rating;
        
        return $this;
    }
    */

    //We need this if we dont use castOnHydrate
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cubicfox");
        $this->setSource("rates");

        $this->hasManyToMany(
            "id",
            "Users",
            "user_id", 
            "product_id",
            "Products",
            "id"
        );
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
            'rating',
            new PresenceOf(
                [
                    'message' => 'Rating is required'
                ]
            )
        );

        $validator->add(
            'rating',
            new DigitValidator(
                [
                    'message' => 'The rating must be numeric',
                ]
            )
        );
        

        $validator->add(
            'rating',
            new Between(
                [
                    'minimum' => 1,
                    'maximum' => 10,
                    'message' => 'The rating must be between 1 and 10',
                ]
            )
        );
    
        return $this->validate($validator);
    }


    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Rates[]|Rates|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Rates|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
