<?php
declare(strict_types=1);

use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Exception;
use Phalcon\Mvc\Model\Resultset;



class ProductsController extends \Phalcon\Mvc\Controller
{
    const PAGE_SIZE_CONSTANT = 2; //very low for good testing purpose


    /** HTTP GET
     * Get a pagable and filterable product list with average ratings 
     * Current client user_id must be in the header (as cubicfox-user)  
     * Optional parameters in http url query. (Since json body is not recommended on GET requests)
     * filter = search in product_code, ownername, product name, product description
     * page = page to get starting from 0. Default = 0. Page size is 20 entries
     * sort = order by this field. Options: name, price. Default = name
     * direction = direction of the order, options: asc, desc. Default = asc
     */
    public function indexAction()
    {   
        
        try
        {
            $response = [];

            //Disable the view
            $this->view->disable();

            //check request type
            if (!$this->request->isGet())
            {
                throw new Exception('Invalid request type', 400);
            }
            
            //Get parameters from url (dont need url_decode())
            $filter = $this->request->getQuery('filter', 'string', null);
            $page = $this->request->getQuery('page', 'int', 0);
            $sort = strtolower($this->request->getQuery('sort', 'string', 'name'));
            $direction = strtolower($this->request->getQuery('direction', 'string', 'asc'));

            //Validate parameters
            $sortValidator = ['name', 'price'];
            $directionValidator = ['asc', 'desc'];

            if ($page < 0)
            {
                throw new Exception('Invalid page parameter', 400);
            }

            if (!in_array($sort, $sortValidator))
            {
                throw new Exception('Invalid sort parameter', 400);
            }

            if (!in_array($direction, $directionValidator))
            {
                throw new Exception('Invalid direction parameter', 400);
            }         

            //Check user_id in header
            if (!$this->request->hasHeader('cubicfox-user')) 
            {
                //throw new Exception('User_id is not defined in header', 400);
            }

            $user_id = $this->request->getHeader('cubicfox-user');

            //Check user_id parameter for valid, existing user
            $user = Users::findFirstById($user_id);
            if (!$user)
            {
                throw new Exception('User not exists', 400);
            }
            if ($filter)
            {
                $products = Products::find(
                    [
                        'conditions' => 
                            '(code LIKE :filter:) OR '.
                            '(name LIKE :filter:) OR '.
                            '(description LIKE :filter:)'
                        ,
                        'order' => $sort.' '.strtoupper($direction),  
                        'limit' => self::PAGE_SIZE_CONSTANT,
                        'offset' => ($page * self::PAGE_SIZE_CONSTANT),
                        'bind' => [
                            'filter' => '%'.$filter.'%',
                        ]
                    ]
                );    
            }
            else
            {
                $products = Products::find(                    
                    [
                        'order' => $sort.' '.strtoupper($direction),  
                        'limit' => self::PAGE_SIZE_CONSTANT,
                        'offset' => ($page * self::PAGE_SIZE_CONSTANT),

                    ]
                );    
            }
            
            $results = [];
            foreach ($products as $product)
            {
                $tmp = $product->toArray();                
                $average = Rates::average(
                    [
                        'column'     => 'rating',
                        'conditions' => 'product_id = ?0',
                        'bind'       => [
                            $product->id
                        ],
                    ]
                );
                $tmp['avg_rating'] = $average; //null if no related rating
                $results[] = $tmp;
            }


            $response['results'] = $results;
            
            $response['status'] = 'OK';
            $response['filter'] = $filter;
            $response['page'] = $page;
            $response['sort'] = $sort;
            $response['direction'] = $direction;

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

    /**
     * HTTP GET:
     * Get product info (selected by id) with all user ratings
     * Current client user_id must be in the header (as cubicfox-user)  
     * 
     * 
     * HTTP PUT:
     * Updates product infos (selected by id) if current user is the its owner
     * Current client user_id must be in the header (as cubicfox-user)  
     */
    
    public function editAction($id = 0)
    {
        try
        {                    
            //Disable the view
            $this->view->disable();

            //id validation
            if (((string)$id != (string)intval($id)) || (intval($id) < 0))
            {
                throw new Exception('Invalid product id', 400);
            }
            $response = [];
    
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
        
            //Interesting Phalcon stuff: all field type is string, need to fix that
            //If we dont enable castOnHydrate we need a custom function like this
            //$user->fixTypes();
    
            //$product = Products::findFirstById($id);
            $product = Products::findFirstById($id);
            if (!$product)
            {
                throw new Exception('Product not exists', 404);
            }

            //Interesting Phalcon stuff: all field type is string, need to fix that
            //If we dont enable castOnHydrate we need a custom function like this
            //$product->fixTypes();

            if ($this->request->isGet())
            {
                //If its a GET then we simply giving product details with its rates

                //If we dont want to hide any field (eg. password, ids..etc) then we can just serialize
                $response['status'] = 'OK';
                
                
                $response['results']['product'] = $product;
                $response['results']['owner'] = $product->users; //lazy loaded relateds?
                $response['results']['rates'] = $product->rates; //lazy loaded relateds?
                
                //It seems there is no hidden/visibilty filter on serialize in Phalcon,
                //so if its important to hide something we must do things manually and iterate through, like this:
                
                /*
                $response['results']['code'] = $product->code;
                $response['results']['name'] = $product->name;
                $response['results']['description'] = $product->description;
                $response['results']['price'] = $product->price;
                $response['results']['user']['name'] = $product->users->name;
                $response['results']['user']['email'] = $product->users->email;
                $response['results']['rates'] = [];
                foreach($product->rates as $rate)
                {
                    $tmp = [];
                    //nested relations?
                    //$tmp['user'] = $rate->users->name;
                    $tmp['rating'] = $rate->rating;
                    $response['results']['rates'][] = $tmp;
                }
                */

                //This also set response content type to json                        
                $this->response->setJsonContent($response);
                $this->response->send();
                    
            }
            else if ($this->request->isPut())
            {
                if (strpos($this->request->getContentType(), 'application/json') === false)
                {
                    throw new Exception('Invalid request body type, JSON needed', 400);
                }

                //If its a PUT then it is an update, check permissons, comparing current user with product owner
                if ($product->user_id != $user->id)
                {
                    throw new Exception('Unauthorized', 403);
                }
                //Allow changing all fields except id and related user_id (whitelist)
                //Seems getPut() auto json_decode() if the content type is json
                //I could not get 
                //$product->update($this->request->getPut())   
                //to work (with blacklists of id and user_id), so I used assign with whitelist and save
                $product->assign($this->request->getPut(), ['code', 'name', 'description', 'price']);
                //Validation in the model
                if ($product->save() === false)
                {
                    $errors = '';
                    foreach ($product->getMessages() as $message) {
                        $errors = $errors.$message->getMessage();
                    }
        
                    throw new Exception('Product update failed:'.$errors, 400);
                }                
                $response['status'] = 'OK';
                $response['results'] = $product;
                
                //This also set response content type to json                        
                $this->response->setJsonContent($response);
                $this->response->send();

            }
            else throw new Exception('Invalid request type', 400);
    
        }catch(\Exception $e)
        {            
            $response = [
                'status' => 'ERROR',
                'error_msg' => $e->getMessage()
            ];
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

