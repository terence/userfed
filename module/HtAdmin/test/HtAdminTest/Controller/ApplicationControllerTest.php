<?php

namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;

class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
	protected $applicationFaceId = 12132132132;
	
	public function testCreateActionSuccess()
	{
		$this->loginAdmin();
		
		$number = $this->createRandomNumber();
		
		$data = array(
			'title' => 'Application Test' . $number,
			'description' => 'Description Test' . $number
		);
		
		$this->assertFlashMessengerHas('Add application successfully.', self::SUCCESS);
		$countAppBefore = $this->countApplication();
		$this->dispatch('/admin/app/create', 'POST', $data);
		$countAppAfter = $this->countApplication();
		
		$this->assertEquals($countAppAfter - 1, $countAppBefore);
		
	}
	
	public function testCreateActionFailed()
	{
		$this->_testAclPermission('/admin/app/create');
		$this->reset();
		
		$number = $this->createRandomNumber();
		/* test validate */
		$data = array(
			'title' => '',
			'description' => 'Description Test' . $number
		);
		$this->loginAdmin();
		$this->dispatch('/admin/app/create', 'POST', $data);
		/* string in web environment <Value is required and can't be empty>
		 * but in php command-line environment character <'> converted to &#039;  */
		$this->assertResponseHasString("Value is required and can&#039;t be empty");
		
		$this->reset();
		$this->loginAdmin();
		/* test database exception*/
		$data['title'] = 'Application Test ' . $number;
		$this->mockDatabaseException('App');
		$this->dispatch('/admin/app/create', 'POST', $data);
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testDeleteActionSuccess()
	{
		$countBeforeCreate = $this->countApplication();
		$application = $this->createApplication();
		$countAfterCreate = $this->countApplication();
		$this->assertEquals($countAfterCreate, $countBeforeCreate + 1);
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Delete application successfully.', self::SUCCESS);
		$this->dispatch('/admin/app/delete/' . $application->getApplicationId());
		$countAfterDispatch = $this->countApplication();
		$this->assertEquals($countAfterDispatch, $countBeforeCreate);
	}
	
	public function testDeleteActionFailed()
	{
		
		$this->_testAclPermission('/admin/app/delete/'  . $this->applicationFaceId);
		$this->reset();
		/* test face application */
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found application with id: %s', $this->applicationFaceId), self::ERROR);
		$this->dispatch('/admin/app/delete/' . $this->applicationFaceId);
		
		/* test database exception */
		$countBeforeCreate = $this->countApplication();
		$application  = $this->createApplication();
		$countAfterCreate = $this->countApplication();
		$this->assertEquals($countAfterCreate - 1 , $countBeforeCreate);
		
		$this->reset();
		$this->loginAdmin();
		$this->mockDatabaseException('App');
		$this->dispatch('/admin/app/delete/' . $application->getApplicationId());
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testEditActionSuccess()
	{
		$application = $this->createApplication();
		$number = $this->createRandomNumber();
		$data = array(
			'application_id' => $application->getApplicationId(),
			'title' => 'Update application test' . $number,
			'description' => 'Update Description Test' . $number
		);
		
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Update application info successfully.', self::SUCCESS);
		$this->dispatch('/admin/app/edit/' . $application->getApplicationId(), 'POST', $data);
		
	}
	
	public function testEditActionFailed()
	{
		$this->_testAclPermission('/admin/app/edit/' . $this->applicationFaceId);
		$this->reset();
		
		/* test not login */
		$this->dispatch('/admin/app/edit/' . $this->applicationFaceId);
		$this->assertRedirectRegex('/\/login/');
		
		/* test face application*/
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found application with id: %s', $this->applicationFaceId), self::ERROR);
		$this->dispatch('/admin/app/edit/' . $this->applicationFaceId);
		
		/* test database exception */
		$this->reset();
		$application = $this->createApplication();
		
		$data = array(
			'application_id' => $application->getApplicationId(),
			'title' => 'Update application test' . $application->getApplicationId(),
			'description' => 'Update Description Test' . $application->getApplicationId()
		);
		
		
		$this->loginAdmin();
		$this->mockDatabaseException('App');
		$this->dispatch('/admin/app/edit/' . $application->getApplicationId(), 'POST', $data);
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testListActionSuccess()
	{
		$this->loginAdmin();
		$this->dispatch('/admin/app');
	}
	
	public function testListActionFailed()
	{
		$this->_testAclPermission('/admin/app');
	}
	
	protected function countApplication()
	{
		/* @var $application \HtAuthentication\Model\Application */
		$application = $this->getApplicationServiceLocator()->get('App');
		return $application->count();
	}
}
