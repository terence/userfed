<?php
/**
 * Provide common methods for password
 */
namespace HtApplication\Model;

class Password
{
	/**
	 * Generate a password
	 * 
	 * @param int $length
	 * @return string 
	 */
	public function generatePassword($length)
	{
	    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789&%$*()#@!";
	    $pass = array(); 
	    $alphaLength = strlen($alphabet) - 1; 
	    for ($i = 0; $i < $length; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    $password = implode($pass); 
	    
	    return $password;
	}
	
	/**
	 * Generate password hash
	 * @param string $password
	 * @return string
	 */
	public function generateHash($password)
	{
		return sha1($password);
	}
}