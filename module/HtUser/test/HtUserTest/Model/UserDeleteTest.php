<?php

namespace HtUserTest\Model;

use HtUser\Model\User;
use AppCore\Mvc\Model\Model;
use PHPUnit_Framework_TestCase;
use HtUserTest\Bootstrap;
/* we need use serviceManager for setup connection to database. */
use HtApplication\Test\AbstractHttpControllerTestCase;

class UserDeleteTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		chdir(ROOT_PATH);
		$this->setApplicationConfig(include ROOT_PATH . '/config/application.config.php');
		parent::setUp();
	}
	
	public function testSoftDeleteSuccess()
	{
		$user = $this->createUserWithInternalAuth();
		$this->assertEquals($user->count(array('user_id' => $user->getUserId())), 1);
		
		$user->delete();
		$this->assertEquals($user->count(array('user_id' => $user->getUserId())), 0);
		
		$user->disableSoftDelete();
		$this->assertEquals($user->count(array('user_id' => $user->getUserId())), 1);
	}
	
	public function testGetDeletedUser()
	{
		/* @var $user User */
		$user = $this->createUserWithInternalAuth();
		$count = $user->count(array('user_id' => $user->getUserId()));
		$this->assertEquals($count, 1);
		
		$userId = $user->getUserId();
		$user->delete();
		$user = $user->getOne(array('user_id' => $userId));
		$this->assertNull($user);
	}
}
