<?php
use Phalcon\Db\Column;
\Phalcon\Mvc\Model::setup(['castOnHydrate' => true]);
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Validation\Validator\Alnum as AlnumValidator;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Numericality as NumericalityValidator;



class Products extends \Phalcon\Mvc\Model
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
     * @var string
     */
    public $code;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var double
     */
    public $price;


    //use this func if we dont castOnHydrate
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
        $this->setSource("products");
        
        $this->belongsTo("user_id", "Users", "id");

        $this->hasMany("id", "Rates", "product_id");

        // Skips only when updating
        
        $this->skipAttributesOnUpdate(
            [
                'id',
                'user_id'
            ]
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
            [
                'code',
                'name',
                'price'
            ],
            new PresenceOf(
                [
                    'message' => [
                        'code' => 'The code is required and must be numeric integer type (6 digits).',
                        'name' => 'The name is required.',
                        'price' => 'The price is required and must be numeric type.',
                    ]
                ]
            )
        );
        
        $validator->add(
            'code',
            new DigitValidator(
                [
                    'message' => 'The code must be integer numeric with 6 digits.',
                ]
            )
        );
        
        $validator->add(
            'price',
            new NumericalityValidator(
                [
                    'message' => 'The price must be numeric.',
                ]
            )
        );
        

        $validator->add(
            'code',
            new Between(
                [
                    'minimum' => 0,
                    'maximum' => 999999,
                    'message' => 'The code must be between 0 and 999999.',
                ]
            )
        );

        $validator->add(
            'price',
            new Between(
                [
                    'minimum' => 0,
                    'maximum' => 999999999,
                    'message' => 'The price must be between 0 and 999999999.',
                ]
            )
        );

        $validator->add(
            'name',
            new StringLength(
                [
                    'max'            => 255,
                    'min'            => 2,
                    'messageMaximum' => 'Name must be less then 255 characters.',
                    'messageMinimum' => 'Name must be more then 2 characters.',
                ]
            )
        );

        $validator->add(
            'name',
            new AlnumValidator(
                [
                    'message' => 'Name must contain only alphanumeric (0-9, a-z, A-Z) characters.',
                ]
            )
        );    
    
    
        return $this->validate($validator);
    }


    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Products[]|Products|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Products|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
