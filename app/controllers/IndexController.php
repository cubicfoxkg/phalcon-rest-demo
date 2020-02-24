<?php
declare(strict_types=1);

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $readme = BASE_PATH.'/README.md';
        if (file_exists($readme))
        {
            //Disable view
            $this->view->disable();
            //Output the readme
            echo file_get_contents($readme);
        }
    }


    public function notfoundAction()
    {
        $response = [
            'status' => 'ERROR',
            'error_msg' => 'Requested page not found'
        ];
        $this->view->disable();
        $this->response->setJsonContent($response);
        $this->response->setStatusCode(404);         
        $this->response->send();
    }

}

