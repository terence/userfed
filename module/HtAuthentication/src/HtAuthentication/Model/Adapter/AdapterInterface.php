<?php
/**
 * Authentication adapter for user.
 * Allow to link to user and work with authentication account management
 */
namespace HtAuthentication\Model\Adapter;

use Zend\Authentication\Adapter\AdapterInterface as ZendAuthenticationAdapterInterface;

interface AdapterInterface extends ZendAuthenticationAdapterInterface
{
	/**
	 * @param \HtUser\Model\User | string $user
	 */
	public function linkUser($user);
	
	/**
	 * Get user linked to this adapter
	 * @return User
	 */
	public function getUser();
	
	public function getAuthenticationAccountId();
}