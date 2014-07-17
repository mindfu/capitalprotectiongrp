<?php

class IndexController extends Zend_Controller_Action
{

    public function indexAction()
    {
        // action body
    	header("Location:/users/login/");
    }
	
	
	public function selectTemplateAction(){
		$this->view->headTitle("Capital Protection");    	
		Zend_Loader::loadClass("CreatePortfolio", array(FORMS_PATH));
		$this->view->form = new CreatePortfolio();
		
    	$this->_helper->layout->setLayout("html5");
	}
	

}

