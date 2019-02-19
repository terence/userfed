<?php

namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;
use HtAdminTest\Bootstrap;

class OrganisationControllerTest extends AbstractHttpControllerTestCase
{
	protected $faceOrganisationId = 1213245453135;
	
	public function testListAction()
	{
		$this->loginAdmin();
		$this->dispatch('/admin/org');
	}
	
	public function testCreateActionSuccess()
	{
		$this->loginAdmin();
		$num = $this->createRandomNumber();
		$data = array(
			'title' => 'Organisation Test ' . $num,
			'description' => 'Organisation Description ' . $num,
			'domain' => 'organistion-test-' . $num . '.com'
		);
		
		$countOrgBefore = $this->countOrganisation();
		$this->dispatch('/admin/org/create', 'POST', $data);
		$countOrgAfter = $this->countOrganisation();
		
		$this->assertLessThan($countOrgAfter, $countOrgBefore);
	}

	public function testCreateActionFailed()
	{
		$url = '/admin/org/create';
		$this->_testAclPermission($url);
		
		$this->reset();
		$this->loginAdmin();
		$num = $this->createRandomNumber();
		$data = array(
			'description' => 'Organisation Description ' . $num,
			'domain' => 'organistion-test-' . $num . '.com'
		);
		
		$this->dispatch($url, 'POST', $data);
		/* MessageError: Value is required and can't be empty */
		$this->assertResponseHasString('Value is required and can&#039;t be empty');
		
		/* Test exception */
		$data['title'] = 'Organisation Test ' . $num;
		$this->reset();
		$this->loginAdmin();
		$this->mockDatabaseException('Org');
		$this->dispatch($url, 'POST', $data);

		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
		
	}
	
	public function testEditActionSuccess()
	{
		$organisation = $this->createOrganisation();
		
		$oldTitle = $organisation->getTitle();
		$organisationId = $organisation->getOrganisationId();
		$num = $this->createRandomNumber();
		$data = array(
			'organisation_id' => $organisationId,
			'title' => 'Update Org Test' . $num,
			'description' => 'Update Org Test' . $num,
			'domain' => 'organistion-update-test' . $num . '.com'
		);
		
		$this->loginAdmin();
		$this->dispatch('/admin/org/edit/' . $organisationId, 'POST', $data);
		
		$this->assertRedirectTo('/admin/org');
		$organisation->load($organisationId);
		$this->assertNotNull($organisation->getLastUpdated());
		$this->assertNotEquals($oldTitle, $organisation->getTitle());
	}
	
	public function testGetServerAction()
	{
		$application = $this->createApplication();
		$server = $this->createServer();
		$this->linkServerToApp($server, $application);
		
		$url = '/admin/org/get-server?application-id=' . $application->getApplicationId();
		$this->_testAclPermission($url);

		$this->loginAdmin();
		$this->dispatch($url);
		$this->assertResponseIsJson();
	}
	
	public function testEditActionFailed()
	{
		
		$url = '/admin/org/edit/';
		$this->_testAclPermission($url . $this->faceOrganisationId);
		
		/* test edit an organisation don't exist. */
		$num = $this->createRandomNumber();
		$data = array(
			'organisation_id' => $this->faceOrganisationId,
			'title' => 'Update Org Test' . $num,
			'description' => 'Update Org Test' . $num,
			'domain' => 'organistion-update-test' . $num . '.com'
		);
		
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('No found organisation with id: %s', $this->faceOrganisationId), self::ERROR);
		$this->dispatch($url . $this->faceOrganisationId, 'POST', $data);
		
		$this->reset();
		$this->loginAdmin();
		$countBeforeCreate = $this->countOrganisation();
		$organisation = $this->createOrganisation();
		$countAfterCreate = $this->countOrganisation();
		$data['organisation_id'] = $organisation->getOrganisationId();
		$this->assertEquals($countAfterCreate - 1, $countBeforeCreate);
		
		$this->mockDatabaseException('Org');
		
		$this->dispatch($url . $organisation->getOrganisationId(), 'POST', $data);
		
		$orgModel = $this->getApplicationServiceLocator()->get('Org');
		$orgModel->load($data['organisation_id']);
		
		$this->assertNotEquals($orgModel->getTitle(), $data['title']);
	}
	
	public function testDeleteActionSuccess()
	{
		$organisation = $this->createOrganisation();
		$organisationId = $organisation->getOrganisationId();
		
		$countOrgBefore = $this->countOrganisation();
		$this->loginAdmin();
		$this->dispatch('/admin/org/delete/' . $organisationId);
		$countOrgAfter = $this->countOrganisation();
		/* $countOrgAfter < $countOrgBefore */
		$this->assertLessThan($countOrgBefore, $countOrgAfter);
		$this->assertRedirectTo('/admin/org');
	}
	
	public function testDeleteActionFailed()
	{
		$url = '/admin/org/delete/';
		$this->_testAclPermission($url . $this->faceOrganisationId);
		
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf("No found organisation with id: %s", $this->faceOrganisationId), self::ERROR);
		$this->dispatch($url . $this->faceOrganisationId);
		
		/* test Database Exception*/ 
		$this->reset();
		$organisation = $this->createOrganisation();
		$this->loginAdmin();
		$this->mockDatabaseException('Org');
		$this->dispatch($url . $organisation->getOrganisationId());
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testGetOrgAction()
	{
		$url = '/admin/org/get-org';
		$this->_testAclPermission($url);
		
		$organisation = $this->createOrganisation();
		$this->loginAdmin();
		$this->dispatch($url, 'GET', array('search' => $organisation->getTitle()));
		
		$this->assertResponseIsJson();
		$object = $this->getResponseObjectFromJson();
		$this->assertLessThan($object->total, 0);
	}
	
	protected function countOrganisation()
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		return $organisation->count();
	}
}
