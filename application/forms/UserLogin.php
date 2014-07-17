<?php
/**
 * Form for user login
 * 
 * @author Chris Scrivo
 * 
 */
class UserLogin extends Zend_Form
{
    public function init()
    {
    	$this->addDecorators(array("ViewHelper"), array("Errors"));
		$this->setMethod('post');
 		
		$username = new Zend_Form_Element_Text("username");
		$username->setRequired(true);
		$username->setFilters(array("StringTrim"));
		$username->setAttrib("class", "form-control");
		$username->setAttrib("placeholder", "Username");
		$username->setAttrib("required", "");
		$username->setLabel("Username: ");
		$this->addElement($username);
		
		$password = new Zend_Form_Element_Password("password");
		$password->setLabel("Password: ");
		$password->setAttrib("required", "");
		$password->setAttrib("class", "form-control");
		$password->setFilters(array("StringTrim"));
		$password->setAttrib("placeholder", "Password");
		$this->addElement($password);
		
 
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Login',
            ));
 
    }
}