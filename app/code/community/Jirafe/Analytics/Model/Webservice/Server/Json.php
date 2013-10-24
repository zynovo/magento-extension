<?php

/**
 * Webservice Server Json Model
 *
 * @category  Jirafe
 * @package   Jirafe_Analytics
 * @copyright Copyright (c) 2013 Jirafe, Inc. (http://jirafe.com/)
 * @author    Richard Loerzel (rloerzel@lyonscg.com)
 */

class Jirafe_Analytics_Model_Webservice_Server_Json extends Zend_Rest_Server
{
    /**
    * Implement Zend_Server_Interface::handle()
    *
    * @param  array $request
    * @throws Zend_Rest_Server_Exception
    * @return string|void
    */    
    public function handle($request = null) {
        
        header("Content-type: application/json");
        // get json
        $request = file_get_contents('php://input');
        $input = json_decode($request, true);

        $calling_args = array($input);

        //get method name from input json
        $this->_method = $input['action'];

        if ($this->_functions[$this->_method] instanceof Zend_Server_Reflection_Method) {
            // get class
            $class = $this->_functions[$this->_method]->getDeclaringClass()->getName();
            // call method 
            $result = $this->_callObjectMethod($class, $calling_args);
        }
        else {
            $result = array(
                "action" => $input['action'], 
                "result" => "error", 
                "resultmessage" => "No such method",
                "order_id" => null
            );
        }
        
        echo json_encode($result);
    }
}