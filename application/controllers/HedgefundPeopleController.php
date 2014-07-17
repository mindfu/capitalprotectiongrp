<?php

class HedgefundPeopleController extends Zend_Controller_Action
{
	public function addAction(){
		$db = Zend_Registry::get("main_db");
		$this->_helper->layout->setLayout("empty");
		Zend_Loader::loadClass("CreateHedgeFundPeople", array(FORMS_PATH));
		$form = new CreateHedgeFundPeople();
		
		if ($form->isValid($_POST)){
			$data = $form->getValidValues($_POST);
			
			$data["date_created"] = date("Y-m-d H:i:s");
			
			$db->insert("hedgefund_people", $data);			
			
			$this->view->result = array("success"=>true);
		}else{
			$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
		}
	}
	
	public function updateAction(){
		$db = Zend_Registry::get("main_db");
		$this->_helper->layout->setLayout("empty");
		Zend_Loader::loadClass("UpdateHedgeFundPeople", array(FORMS_PATH));
		$form = new UpdateHedgeFundPeople();
		
		if ($form->isValid($_POST)){
			$data = array(
				"first_name"=>$_POST["first_name"],
				"last_name"=>$_POST["last_name"],
				"hedgefund_id"=>$_POST["hedgefund_id"],
				"title"=>$_POST["title"]
			);
			
			$db->update("hedgefund_people", $db->quoteInto("id = ?", $_POST["id"]));
			$this->view->result = array("success"=>true);
		}else{
			$this->view->result = array("success"=>false, "errors"=>$form->getErrors());
		}
		
		
	}
}