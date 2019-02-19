<?php

namespace HtAdminTest\Controller;

use HtAdmin\Test\AbstractHttpControllerTestCase;
use HtAuthentication\Model\Organisation;

class ApplicationOrganisationControllerTest extends AbstractHttpControllerTestCase
{
	public function testAddOrgActionSuccess()
	{
		$server = $this->createServer();
		
		$application = $this->createApplication();
		
		$this->linkServerToApp($server, $application);
		
		$organisation = $this->createOrganisation();
		
		$this->loginAdmin();
		$this->dispatch(
				'/admin/app-org/add-org/' . $application->getApplicationId() ,
				'POST',
				array('server_id' => $server->getServerId(), 'organisation_id' => $organisation->getOrganisationId()
		));
	}
	
	public function testAddOrgActionFailed()
	{
		$url = '/admin/app-org/add-org/';
		
		$this->_testAclPermission($url . $this->faceApplicationId);
		$this->reset();
		
		/* test method isn't '*/
		$this->loginAdmin();
		$this->dispatch($url . $this->faceApplicationId);
		$this->assertRedirectTo('/admin/app/org/' . $this->faceApplicationId);
		
		$this->reset();
		$this->loginAdmin();
		/* test application invalid. */
		$this->assertFlashMessengerHas(sprintf('Not found application with id: %s', $this->faceApplicationId), self::ERROR);
		$this->dispatch($url . $this->faceApplicationId, 'POST', array('server_id' => $this->faceServerId, 'organisation_id' => $this->faceOrganisationId));
		
		/* test organisation invalid */
		$this->reset();
		$this->loginAdmin();
		$application = $this->createApplication();
		$applicationId = $application->getApplicationId();
		$this->assertFlashMessengerHas(sprintf('Not found organisation with id: %s', $this->faceOrganisationId), self::ERROR);
		$this->dispatch($url . $applicationId, 'POST', array('server_id' => $this->faceServerId, 'organisation_id' => $this->faceOrganisationId ));
		
		/* test server invalid. */
		$this->reset();
		$this->loginAdmin();
		$organisation = $this->createOrganisation();
		$organisationId = $organisation->getOrganisationId();
		$this->assertFlashMessengerHas(sprintf('Not found server with id: %s', $this->faceServerId), self::ERROR);
		$this->dispatch($url . $applicationId, 'POST', array('server_id' => $this->faceServerId, 'organisation_id' => $organisationId));
		
		/* test application and server */
		$this->reset();
		$this->loginAdmin();
		$server = $this->createServer();
		$serverId = $server->getServerId();
		
		$this->assertFlashMessengerHas(
				sprintf('<b>%s</b> has not <b>%s</b> server.',
						$application->getTitle(),
						$server->getDomain()),
				self::ERROR);
		$this->dispatch($url . $applicationId, 'POST', array('server_id' => $serverId, 'organisation_id' => $organisationId ));
		
		/* test database exception*/
		$this->linkServerToApp($server, $application);
		$this->mockDatabaseException('AppServerOrg');
		$this->dispatch($url . $applicationId, 'POST', array('server_id' => $serverId, 'organisation_id' => $organisationId ));
	}
	
	public function testDeleteOrgActionSuccess()
	{
		$appServerOrg = $this->linkOrgToAppServer();
		
		$this->loginAdmin();
		$countAppServerOrgBefore = $this->countAppServerOrg();
		$this->dispatch('/admin/app-org/delete-org/' . $appServerOrg->getApplicationServerOrganisationId());
		$countAppServerOrgAfter = $this->countAppServerOrg();
		
		$this->assertLessThan($countAppServerOrgBefore, $countAppServerOrgAfter);
	}
	
	public function testDeleteOrgActionFailed()
	{
		$url = '/admin/app-org/delete-org/';
		$appServerOrgId = '13565465465'; // face id
		$this->_testAclPermission($url . $appServerOrgId);
		
		$this->loginAdmin();
		$countAppServerOrgBefore = $this->countAppServerOrg();
		$this->dispatch($url . $appServerOrgId);
		$countAppServerOrgAfter = $this->countAppServerOrg();
		
		$this->assertEquals($countAppServerOrgAfter, $countAppServerOrgBefore);
		
		/* test database exception. */
		$appServerOrg = $this->linkOrgToAppServer();
		$this->mockDatabaseException('AppServerOrg');
		$countAppServerOrgBefore = $this->countAppServerOrg();
		$this->dispatch($url . $appServerOrg->getApplicationServerOrganisationId());
		$countAppServerOrgAfter = $this->countAppServerOrg();
		
		$this->assertEquals($countAppServerOrgAfter, $countAppServerOrgBefore);
	}
	
	public function testViewOrgActionSuccess()
	{
		$organisation = $this->createOrganisation();
		$this->loginAdmin();
		$this->dispatch('/admin/org/app/' . $organisation->getOrganisationId());
		$this->assertResponseHasString($organisation->getTitle());
	}
	
	public function testViewOrgActionFailed()
	{
		
		$url = '/admin/org/app/';
		$this->_testAclPermission( $url . $this->faceOrganisationId);
		
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found organisation with id: %s', $this->faceOrganisationId), self::ERROR);
		$this->dispatch($url . $this->faceOrganisationId);
		$this->assertRedirectTo('/admin/org');
		
	}
	
	public function testAddApplicationActionSuccess()
	{
		$organisation = $this->createOrganisation();
		$url = '/admin/app-org/add-application/' . $organisation->getOrganisationId();
		
		$application = $this->createApplication();
		$server = $this->createServer();
		$this->linkServerToApp($server, $application);
		
		$data = array(
			'application_id' => $application->getApplicationId(),
			'server_id' => $server->getServerId()
		);
		
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Add application for ogranisation successfully.', self::SUCCESS);
		$countBefore = $this->countAppByOrg($organisation);
		$this->dispatch($url, 'POST', $data);
		$countAfter = $this->countAppByOrg($organisation);
		
		$this->assertEquals($countAfter - 1, $countBefore);
	}
	
	protected $faceOrganisationId = 3213254653213;
	protected $faceApplicationId = 31546532132132;
	protected $faceServerId = 315451321354;
	
	public function  testAddApplicationActionFailed()
	{
		$url = '/admin/app-org/add-application/';
		$this->_testAclPermission($url . $this->faceOrganisationId);
		
		$data = array(
			'application_id' => $this->faceApplicationId,
			'server_id' => $this->faceServerId
		);
		$this->reset();
		$this->loginAdmin();
		$this->dispatch($url . $this->faceOrganisationId);
		$this->assertRedirectTo('/admin/org/app/' . $this->faceOrganisationId);
		
		/* test face organisation */
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found organisation with id: %s', $this->faceOrganisationId), self::ERROR);
		$this->dispatch($url . $this->faceOrganisationId, 'POST', $data);

		/* test face application */
		$this->reset();
		$this->loginAdmin();
		$organisation = $this->createOrganisation();
		$url .= $organisation->getOrganisationId();
		$this->assertFlashMessengerHas(sprintf('Not found application with id: %s', $this->faceApplicationId), self::ERROR);
		$this->dispatch($url, 'POST', $data);
		
		/* test face server */
		$this->reset();
		$this->loginAdmin();
		$application = $this->createApplication();
		$this->assertFlashMessengerHas(sprintf('Not found server with id: %s', $this->faceServerId), self::ERROR);
		$data['application_id'] = $application->getApplicationId();
		$this->dispatch($url, 'POST', $data);
		
		/* test application has permission access to server */
		$this->reset();
		$this->loginAdmin();
		$server = $this->createServer();
		$data['server_id'] = $server->getServerId();
		$this->assertFlashMessengerHas(
				sprintf('<b>%s</b> has not <b>%s</b> server.', $application->getTitle(), $server->getDomain()),
				self::ERROR
				);
		$this->dispatch($url, 'POST', $data);
		
		$this->reset();
		$this->loginAdmin();
		$this->linkServerToApp($server, $application);
		$this->linkOrgToAppServer($organisation, $server, $application);
		/* tets organisation already has permission access into server.*/
		$this->assertFlashMessengerHas('This organisation already has permission access this server.', self::ERROR);
		$this->dispatch($url, 'POST', $data);
		
		/* test database exception */
		$this->reset();
		$this->loginAdmin();
		$newOrganisation = $this->createOrganisation();
		$this->mockDatabaseException('AppServerOrg');
		$this->dispatch('/admin/app-org/add-application/' . $newOrganisation->getOrganisationId(),
				'POST',
				$data);
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
		
	}
	
	public function testDeleteApplicationSuccess()
	{
		$appServerOrg = $this->linkOrgToAppServer();
		$this->loginAdmin();
		$this->assertFlashMessengerHas('Delete application successfully.', self::SUCCESS);
		$countBefore = $this->countAppByOrg($appServerOrg->getOrganisationId());
		$this->dispatch('/admin/app-org/delete-application/' . $appServerOrg->getApplicationServerOrganisationId());
		$countAfter = $this->countAppByOrg($appServerOrg->getOrganisationId());
		$this->assertEquals($countAfter, $countBefore - 1);
	}
	
	public function testDeleteApplicationFailed()
	{
		$appServerOrgId = 213213543654; // face id
		
		$url = '/admin/app-org/delete-application/';
		$this->_testAclPermission($url . $appServerOrgId);
		
		$this->reset();
		$this->loginAdmin();
		
		$this->assertFlashMessengerHas(sprintf('Cannot found application server organisation with id %s', $appServerOrgId), self::ERROR);
		$this->dispatch($url . $appServerOrgId);
		
		$this->reset();
		$this->loginAdmin();
		$appServerOrg = $this->linkOrgToAppServer();
		$this->mockDatabaseException('AppServerOrg');
		$this->dispatch($url . $appServerOrg->getApplicationServerOrganisationId());
		$this->assertResponseStatusCode(500);
		$this->assertResponseHasString(self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testGetServerOrgAction()
	{
		$appServerOrg = $this->linkOrgToAppServer();
		$url = '/admin/app-org/get-server-org/';
		
		$this->loginAdmin();
		$this->dispatch($url . $appServerOrg->getApplicationId(),
				'GET',
				array('org-id' => $appServerOrg->getOrganisationId())
				);
		
		$this->assertResponseIsJson();
		$json = $this->getResponseObjectFromJson();
		$this->assertEquals(count($json->servers), 1);
	}
	
	public function testGetServerAction()
	{
		$url = '/admin/app-org/get-server/:applicationId?org-id=organisationId';
		
		$appServerOrg = $this->linkOrgToAppServer();
		$this->loginAdmin();
		$this->dispatch($url . $appServerOrg->getApplicationId(),
				'GET',
				array('org-id' => $appServerOrg->getOrganisationId()));
		$this->assertResponseIsJson();
		$json = $this->getResponseObjectFromJson();
		$this->assertEquals(count($json->servers), 0);
	}
	
	public function testViewAppActionSuccess()
	{
		$appServerOrg = $this->linkOrgToAppServer();
		
		$url = '/admin/app-org/view-app/' . $appServerOrg->getApplicationId();
		
		$this->loginAdmin();
		$this->dispatch($url);
		$this->assertResponseStatusCode(200);
	}
	
	public function testViewAppActionFailed()
	{
		$url = '/admin/app-org/view-app/';
		
		$this->_testAclPermission($url . $this->faceApplicationId);
		
		$this->reset();
		$this->loginAdmin();
		$this->assertFlashMessengerHas(sprintf('Not found application with id: %s', $this->faceApplicationId), self::ERROR);
		$this->dispatch($url . $this->faceApplicationId);
	}
	
	public function testGetApplicationAction()
	{
		$url = '/admin/app-org/get-application';
		$this->_testAclPermission($url);
		
		$this->reset();
		$this->loginAdmin();
		
		$application = $this->createApplication();
		
		$this->dispatch($url, 'GET', array('key' => $application->getTitle()));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->total, 1);
	}


	/**
	 * @return int
	 */
	protected function countAppServerOrg()
	{
		/* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisation */
		$appServerOrg = $this->getApplicationServiceLocator()->get('AppServerOrg');
		return $appServerOrg->count();
	}
	
	/**
	 * 
	 * @param int HtAuthentication\Model\Organisation $organisation
	 */
	protected function countAppByOrg($organisation)
	{
		if ($organisation instanceof Organisation) {
			$organisation = $organisation->getOrganisationId();
		}
		
		/* @var $appOrgServer \HtAuthentication\Model\ApplicationServerOrganisation */
		$appOrgServer = $this->getApplicationServiceLocator()->get('AppServerOrg');
		return $appOrgServer->count(array('organisation_id' => $organisation));
	}
	
	/**
	 * @param int | null | \HtAuthentication\Model\Organisation $organisation
	 * @param int | null | \HtAuthentication\Model\Server $server
	 * @param int | null | \HtAuthentication\Model\Application $application
	 * @return \HtAuthentication\Model\ApplicationServerOrganisation
	 */
	protected function linkOrgToAppServer($organisation = null, $server = null, $application = null)
	{
		$sl = $this->getApplicationServiceLocator();
		
		$appServerOrg = $sl->get('AppServerOrg');
		/* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisation */
		
		if ($organisation === null) {
			$organisation = $this->createOrganisation();
			$organisationId = $organisation->getOrganisationId();
		} elseif ($organisation instanceof \HtAuthentication\Model\Organisation) {
			$organisationId = $organisation->getOrganisationId();
		} else {
			$organisationId = (int) $organisation;
		}
		
		if ($server === null) {
			$server = $this->createServer();
			$serverId = $server->getServerId();
		} elseif ($server instanceof \HtAuthentication\Model\Server) {
			$serverId = $server->getServerId();
		} else {
			$serverId = (int) $server;
		}
		
		if ($application === null) {
			$application = $this->createApplication();
			$applicationId = $application->getApplicationId();
		} elseif ($application instanceof \HtAuthentication\Model\Application) {
			$applicationId = $application->getApplicationId();
		} else {
			$applicationId = (int) $application;
		}
		
		$this->linkServerToApp($server, $application);
		
		$appServerOrg->setOrganisationId($organisationId)
					->setApplicationId($applicationId)
					->setServerId($serverId)
					->save();
		
		return $appServerOrg;
	}
}
