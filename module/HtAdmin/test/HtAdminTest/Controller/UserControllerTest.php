<?php

namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;

class UserControllerTest extends AbstractHttpControllerTestCase
{
	public function testDeleteAction()
	{
		$countBeforeCreate = $this->countUser();
		$user = $this->createUserWithInternalAuth();
		$countAfterCreate = $this->countUser();
		$this->loginAdmin();
		$this->dispatch('/admin/user/delete/' . $user->getUserId());
		$countAfterDelete = $this->countUser();
		$this->assertEquals($countAfterCreate -1 , $countBeforeCreate);
		$this->assertEquals($countAfterDelete, $countBeforeCreate);
	}
	
	public function testHardDeleteAtion()
	{
		$includeSoftDeletedUser = true;
		$countBeforeCreate = $this->countUser($includeSoftDeletedUser);
		$user = $this->createUserWithInternalAuth();
		$countAfterCreate = $this->countUser($includeSoftDeletedUser);
		$this->loginAdmin();
		$this->dispatch('/admin/user/permanently-delete/' . $user->getUserId());
		$countAfterDelete = $this->countUser($includeSoftDeletedUser);
		$this->assertEquals($countAfterCreate -1 , $countBeforeCreate);
		$this->assertEquals($countAfterDelete, $countBeforeCreate);
		
		$this->reset();
		
		/* Delete an user logged */
		$user = $this->createUserWithInternalAuth();
		$this->logUserIn($user->toStdClass());
		$user->hardDelete();
		$this->assertFlashMessengerHas('Your account has been deleted.', self::ERROR);
		$this->dispatch('/profile');
		$this->assertRedirect();
	}
	
	protected function countUser($includeSoftDeletedUser = false)
	{
		/* @var $user \HtUser\Model\User */
		$user = $this->getApplicationServiceLocator()->get('User');
		if ($includeSoftDeletedUser) {
			$user->disableSoftDelete();
		}
		return $user->count();
	}
	
	public function testRestoreUserAction()
	{
		$countBeforeCreate = $this->countUser();
		$user = $this->createUserWithInternalAuth();
		$countAfterCreate = $this->countUser();
		$this->assertEquals($countAfterCreate - 1, $countBeforeCreate);
		$this->loginAdmin();
		$this->dispatch('/admin/user/delete/' . $user->getUserId());
		$countAfterDelete = $this->countUser();
		$this->assertEquals($countBeforeCreate, $countAfterDelete);
		$this->reset();
		$this->loginAdmin();
		$this->dispatch('/admin/user/restore/' . $user->getUserId());
		$countAfterRestore = $this->countUser();
		$this->assertEquals($countAfterRestore, $countAfterDelete + 1);
	}
}
