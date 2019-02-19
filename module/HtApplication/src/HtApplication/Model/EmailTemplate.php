<?php
namespace HtApplication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;

class EmailTemplate extends AppCoreModel
{
	/**
	 * Only common template between applications place here.
	 * Application specific templates should place in Application code
	 */
 	const ADMIN_CREATE_USER_MAIL_TEMPLATE = "amdin_create_user_mail_template";
	const ADMIN_REGENERATE_PASSWORD = "admin_regenerate_password";
	
	const FORGOT_PASSWORD_EMAIL_TEMPLATE = "forgot_password_email_template";
	const ACTIVATION_MAIL_TEMPLATE = "activation_mail_template";
	const SETUP_USER_MAIL_TEMPLATE = "setup_user_mail_template";
		
    public $id;
    public $name;
    public $body;
    public $subject;
    public $code;

    protected $mapping = array(
    		array('dbColumn' => 'email_template_id', 'objectProperty' => 'id', 'isIdentifier' => true),
    		array('dbColumn' => 'name', 'objectProperty' => 'name'),
    		array('dbColumn' => 'body', 'objectProperty' => 'body'),
    		array('dbColumn' => 'subject', 'objectProperty' => 'subject'),
    		array('dbColumn' => 'code', 'objectProperty' => 'code'),
    );
    
	/**
	 * get body/subject of a email template
	 * 
	 * 
	 * @param string $templateCode
	 * @return object or null
	 */
    public function getTemplate($templateCode)
    {
    	$rowset = $this->getTable()->select(array(
    		'code' => $templateCode,
    	));
    	$result = $rowset->current();
    	if($result) {
    		return $result;
    	}
    	
    	return null;
    }
    
    /**
     * 
     * Build email message from email template
     * 
     * @param string $templateContent
     * @param aray $params
     */        
    public function buildMessage($templateCode, $params)
    {
    	$template = $this->getTemplate($templateCode);
    	$footer = $this->getTemplate('footer');
    	$body = $template->body;
		$params['footer'] = $footer->body;
		if(!strpos($body, "{footer}")) {
			$body .= "\r\n<p>{footer}</p>";
		}
    	$subject = $template->subject;
    	foreach ($params as $key => $value) {
    		if(strpos($body, "{" . $key . "}")) {
    			$body = str_replace("{" . $key . "}", $value, $body);
    		}
    		if(strpos($subject, "{" . $key . "}")) {
    			$subject = str_replace("{" . $key . "}", $value, $subject);
    		}
    	}
    	
    	return array(
    		'body' => $body,
    		'subject' => $subject
    	);
    }
}