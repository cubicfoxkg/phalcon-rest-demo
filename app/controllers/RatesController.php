<?php
declare(strict_types=1);

class RatesController extends \Phalcon\Mvc\Controller
{

    /**
     * HTTP POST
     * Given user (cubic-user from header) gives a rating (1-10 in request json body) for the given product (url id).
     * If the rating is already exists update its value, if not then create a new rating
     */

    public function rateAction($id = 0)
    {
        try
        {            
            $response = [];

            //Disable the view
            $this->view->disable();

            //id validation
            if (((string)$id != (string)intval($id)) || (intval($id) < 0))
            {
                throw new Exception('Invalid product id', 400);
            }

            //check request type
            if (!$this->request->isPost())
            {
                throw new Exception('Invalid request type', 400);
            }

            //Check if request body is JSON
            if (strpos($this->request->getContentType(), 'application/json') === false)
            {
                throw new Exception('Invalid request body type, JSON needed', 400);
            }

            //Check user_id in header
            if (!$this->request->hasHeader('cubicfox-user')) 
            {
                throw new Exception('User_id is not defined in header', 400);
            }

            $user_id = $this->request->getHeader('cubicfox-user');
            if (((string)$user_id != (string)intval($user_id)) || (intval($user_id) < 0))
            {
                throw new Exception('User_id is not integer positive numeric', 400);
            }
            //Check user_id parameter for valid, existing user
            $user = Users::findFirstById($user_id);
            if (!$user)
            {
                throw new Exception('User not exists', 400);
            }
            
            //Check id parameter for valid, existing product
            $product = Products::findFirstById($id);
            if (!$product)
            {
                throw new Exception('Product not exists', 404);
            }

            //Check for existing rate
            $rate = Rates::findFirst(
                [
                    'conditions' => 
                        'user_id = :user_id: AND '.
                        'product_id = :product_id:'                    
                    ,
                    'bind'       => [
                        'user_id' => $user_id,
                        'product_id' => $id
                    ]
                ]
            );
            //Interesting Phalcon stuff: all field type is string, need to fix that
            //If we dont enable castOnHydrate we need a custom function like this
            //$rate->fixTypes();
            if ($rate)
            {
                //User already voted for this product, update old rating for this new
                //POST request with json can be accessed with getPut(), getPost() wont give any data 
                //Seems getPut() auto json_decode() if the content type is json
                //Only assisn rating field (whitelist)
                $rate->assign($this->request->getPut(), ['rating']);
                //Validation in the model
                if ($rate->save() === false)
                {
                    $errors = '';
                    foreach ($rate->getMessages() as $message) {
                        $errors = $errors.$message->getMessage();
                    }
        
                    throw new Exception('Rate update failed:'.$errors, 400);
                }                
            }
            else
            {                
                //New Rate entry for this product and this user
                $rate = new Rates();
                //POST request with json can be accessed with getPut(), getPost() wont give any data 
                //Seems getPut() auto json_decode() if the content type is json
                //Only assisn rating field (whitelist)
                $rate->assign($this->request->getPut(), ['rating']);
                $rate->user_id = $user_id;
                $rate->product_id = $id;
                //Validation in the model
                if ($rate->save() === false)
                {
                    $errors = '';
                    foreach ($rate->getMessages() as $message) {
                        $errors = $errors.$message->getMessage();
                    }
        
                    throw new Exception('Rate update failed:'.$errors, 400);
                }                
            }
            
            $response['status'] = 'OK';                
            $response['results']['rate'] = $rate;
            //This also set response content type to json                        
            $this->response->setJsonContent($response);
            $this->response->send();
        
        }catch(\Exception $e)
        {
            $response = [
                'status' => 'ERROR',
                'error_msg' => $e->getMessage()
            ];
            //This also set response content type to json                        
            $this->response->setJsonContent($response);
            $ecode = intval($e->getCode());
            //If it is not in our used http codes its 500, aka internal server error
            if ((!$ecode) || (!in_array($ecode, [400, 401, 403, 404])))
            {
                $ecode = 500;
            }
            $this->response->setStatusCode($ecode);
            $this->response->send();
        }

    }

}

