<?php

namespace HtAdminTest\Controller;
use HtAdmin\Test\AbstractHttpControllerTestCase;
use HtAuthentication\Model\Organisation;
use HtUser\Model\User;

class UserOrgnisationControllerTest extends AbstractHttpControllerTestCase
{
	protected $faceUserId = 31323435431;
	protected $faceOrganisationId = 135412154432;
	public function testAddUserActionSuccess()
	{
		$email = 'user_test_' . $this->createRandomNumber() . '@gmail.com';
		
		$user = $this->createUserWithInternalAuth($email);
		$organisation = $this->createOrganisation();
		$this->loginAdmin();
		
		$countUserBefore = $this->countUserByOrg($organisation);
		$this->assertFlashMessengerHas('Add user successfully.', self::SUCCESS);
		$this->dispatch('/admin/user-org/add-user/' . $organisation->getOrganisationId(), 'POST', array('user_id' => $user->getUserId()));
		$countUserAfter = $this->countUserByOrg($organisation);
		
		$this->assertEquals($countUserAfter - 1, $countUserBefore);
	}
	
	public function testAddUserActionFailed()
	{
		$url = '/admin/user-org/add-user/';
		$this->_testAclPermission($url . $this->faceUserId);
		
		/* test method isn't POST*/
		$this->reset();
		$this->loginAdmin();
		$this->dispatch($url . $this->faceUserId);
		
		/* test face organisation */
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found organisation with id: %s', $this->faceOrganisationId), self::ERROR);
		$this->dispatch($url . $this->faceOrganisationId, 'POST', array('user_id' => $this->faceUserId));
		$this->reset();
		/* test face user */
		$this->loginAdmin();
		$organisation = $this->createOrganisation();
		$organisationId = $organisation->getOrganisationId();
		$this->assertFlashMessengerHas(sprintf('Not found user with id %s', $this->faceUserId), self::ERROR);
		$this->dispatch($url . $organisationId, 'POST', array('user_id' => $this->faceUserId));
		
		/* test user already is member of organisation. */
		$this->reset();
		$this->loginAdmin();
		$user = $this->createUserWithInternalAuth();
		$userId = $user->getUserId();
		$this->linkUserToOrg($user, $organisation);
		
		$this->assertFlashMessengerHas('This user already is member of organisation.', self::ERROR);
		$this->dispatch($url . $organisationId, 'POST', array('user_id' => $userId));
		
		/* test database exception*/
		$this->reset();
		$this->loginAdmin();
		$newUser = $this->createUserWithInternalAuth();
		$countUserBefore = $this->countUserByOrg($organisation);
		$this->mockDatabaseException('Org', 'addUser');
		$this->dispatch($url . $organisationId, 'POST', array('user_id' => $newUser->getUserId()));
		$countUserAfter = $this->countUserByOrg($organisation);
		
		$this->assertEquals($countUserAfter, $countUserBefore);
	}
	
	public function testDeleteUserActionSuccess()
	{
		$user = $this->createUserWithInternalAuth();
		$organisation = $this->createOrganisation();
		$userOrganisation = $this->linkUserToOrg($user, $organisation);
		
		$this->loginAdmin();
		
		$countUserBefore = $this->countUserByOrg($organisation);
		$this->assertFlashMessengerHas('User has been remove from organisation.', self::SUCCESS);
		$this->dispatch('/admin/user-org/delete-user/' . $userOrganisation->getUserOrganisationId());
		$countUserAfter = $this->countUserByOrg($organisation);
		
		$this->assertEquals($countUserAfter, $countUserBefore - 1);
	}
	
	public function testDeleteUserActionFailed()
	{
		$userOrganisationId = '13213214'; // face userOrganistionId
		
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found id %s', $userOrganisationId), self::ERROR);
		$this->dispatch('/admin/user-org/delete-user/' . $userOrganisationId);
		
		/* test database exception */
		$this->reset();
		$this->loginAdmin();
		$organisation = $this->createOrganisation();
		$user = $this->createUserWithInternalAuth();
		
		$countBeforeCreate = $this->countUserByOrg($organisation);
		$userOrganisation = $this->linkUserToOrg($user, $organisation);
		$countAfterCreate = $this->countUserByOrg($organisation);
		
		$this->assertEquals($countAfterCreate, $countBeforeCreate + 1);

		$this->mockDatabaseException('UserOrg');
		$this->dispatch('/admin/user-org/delete-user/' . $userOrganisation->getUserOrganisationId());
		$countAfterDelete = $this->countUserByOrg($organisation);
		
		$this->assertEquals($countAfterCreate, $countAfterDelete);
	}
	
	public function testGetUserAction()
	{
		$organisation = $this->createOrganisation();
		$user = $this->createUserWithInternalAuth();
		
		$this->loginAdmin();
		$this->dispatch(
				'/admin/user-org/get-user/' . $organisation->getOrganisationId(),
				'GET', array( 'search' => $user->getEmail())
				);
		$this->assertResponseIsJson();
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->total, 1);
	}

	public function	testViewOrgActionSuccess()
	{
		$organisation = $this->createOrganisation();
		$this->loginAdmin();
		$this->dispatch('/admin/user-org/view-org/' . $organisation->getOrganisationId());
		$this->assertResponseHasString($organisation->getTitle());
	}
	
	public function testViewOrgActionFailed()
	{
		$organisationId = '121321354'; // face organisationId
		/* test not login. */
		$this->dispatch('/admin/user-org/view-org/' . $organisationId);
		$this->assertRedirectRegex('/\/login/');
		/* test faceId*/
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found organisation with id: %s', $organisationId), self::ERROR);
		$this->dispatch('/admin/user-org/view-org/' . $organisationId);
	}
	
	public function testGetOrganisationAction()
	{
		$userId = '321321';
		$this->loginAdmin();
		$this->dispatch('/admin/user-org/get-organisation/' . $userId);
		$this->assertResponseIsJson();
	}

	protected function countUserByOrg($organisation)
	{
		$sl = $this->getApplicationServiceLocator();
		
		/* @var $organisation \HtAuthentication\Model\UserOrganisation */
		if ($organisation instanceof Organisation) {
			$organisationId = $organisation->getOrganisationId();
		} else {
			$organisationId = (int) $organisation;
		}
		
		$userOrganisation = $sl->get('UserOrg');
		return $userOrganisation->count(array('organisation_id' => $organisationId));
	}
	
	/**
	 * if $user and $organisation is null method auto create new an user and new an organisation.
	 * @param int | null | \HtUser\Model\User $user
	 * @param int | null | \HtAuthentication\Model\Organisation $organisation
	 * @return \HtAuthentication\Model\UserOrganisation
	 */
	protected function linkUserToOrg($user = null, $organisation = null)
	{
		if ($user === null) {
			$email = 'user_test_' . $this->createRandomNumber() . '@gmail.com';
			$user = $this->createUserWithInternalAuth($email);
			$userId = $user->getUserId();
		} elseif ($user instanceof User) {
			$userId = $user->getUserId();
		} else {
			$userId = (int) $user;
		}
		
		if ($organisation === null) {
			$organisation = $this->createOrganisation();
			$organisationId = $organisation->getOrganisationId();
		} elseif ($organisation instanceof Organisation) {
			$organisationId = $organisation->getOrganisationId();
		} else {
			$organisationId = (int) $organisation;
		}
		/* @var  $userOrganisation \HtAuthentication\Model\UserOrganisation */
		$userOrganisation = $this->getApplicationServiceLocator()->get('UserOrg');
		$userOrganisation->setUserId($userId)
				->setOrganisationId($organisationId)
				->save();
		
		return $userOrganisation;
	}
}
