<?php

namespace HtAuthenticationTest\Controller;

use HtApplication\Test\AbstractHttpControllerTestCase;

class ApiOrganisationControllerTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		/**
		 * We use style in Zend\Test 2.2 but not work
		 * @see http://framework.zend.com/manual/2.2/en/modules/zend.test.phpunit.html
		 */
//		$this->setApplicationConfig(
//			include 'D:\tuan-data\projects\repos\user-federation\userfed\code\config\application.config.php'
//		);
		/**
		 * So we mix the 2.0 way with 2.2 way
		 * http://framework.zend.com/manual/2.0/en/user-guide/unit-testing.html 
		 */
		chdir(ROOT_PATH);
		$this->setApplicationConfig(include ROOT_PATH . '/config/application.config.php');
		parent::setUp();
	}
        
        protected function getValidServerKey()
        {
            return [
                'WS_server_id' => 'f100d8c5c68684f4770ba66bf90be2c9',
                'WS_server_ip' => $this->createIpv4(),
            ];
        }
        
        protected function getInValidServerKey()
        {
            return [
                'WS_server_id' => 'invalid secret',
                'WS_server_ip' => $this->createIpv4(),
            ];
        }


        public function testOrgCreateActionSuccess()
	{
		$countOrgBefore = $this->countOrganisation();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/create', 'GET', array(
			'organisation_name' => 'Organisation name test',
		));
		$contents = $this->getResponseObjectFromJson();
		$countOrgAfter = $this->countOrganisation();
		$this->assertEquals($contents->result, 1);
		$this->assertEquals($countOrgBefore, $countOrgAfter - 1);
	}
	
	public function testOrgCreateActionFailed()
	{
		$this->dispatch('/api/org/create');
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
		$this->mockDatabaseException('Org');
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/create', 'GET', array(
			'organisation_name' => 'Organisation created by API ' . $this->createRandomNumber(),
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		$this->assertContains($contents->message, self::DATABASE_EXCEPTION_MESSAGE);
		
	}
	
	protected function countOrganisation()
	{
		$organisation = $this->getApplicationServiceLocator()->get('Org');
		return $organisation->count();
	}
	
	public function testOrgUpdateActionSuccess()
	{
		$organisation = $this->createOrganisation();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$data = array(
			'organisation_id' => $organisation->getOrganisationId(),
			'organisation_name' => 'Organisation name test update ' . $this->createRandomNumber(),
			'organisation_description' => 'Organisation description test update',
			'domain' => 'test.domain.com',
		);
		$this->dispatch('/api/org/update', 'GET', $data);
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
	}
	
	public function testOrgUpdateActionFailed()
	{
		$organisation = $this->createOrganisation();
		$data = array_merge(array(
			'organisation_name' => 'Organisation name test update',
			'organisation_description' => 'Organisation description test update',
			'domain' => 'test.domain.com',
		), $this->getValidServerKey());
		$this->dispatch('/api/org/update', 'GET', $data);
		
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
		$data['organisation_id'] = $organisation->getOrganisationId();
		$data['organisation_name'] = '';
		$this->dispatch('/api/org/update', 'GET', $data);
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
		$data['organisation_name'] = 'Organisation name test update ' . $this->createRandomNumber();
		$this->mockDatabaseException('Org');
		$this->dispatch('/api/org/update', 'GET', $data);
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		$this->assertContains($contents->message, self::DATABASE_EXCEPTION_MESSAGE);
	}
	
	public function testOrgExistAction()
	{
		$organisation = $this->createOrganisation();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/exist', 'GET', array(
			'organisation_id' => $organisation->getOrganisationId(),
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
		$this->reset();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/exist', 'GET', array(
			'organisation_name' => $organisation->getTitle(),
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
		
		$this->reset();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/exist', 'GET', array(
			'organisation_id' => 12135321325,
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/exist', 'GET', array(
			'organisation_name' => 'ncdjejnchdjekfkcmdjesmjdmc',
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
	}
	
	public function testOrgDeleteActionSuccess()
	{
		$organisation = $this->createOrganisation();
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/delete', 'GET', array(
			'organisation_id' => $organisation->getOrganisationId(),
		));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 1);
	}
	
	public function testOrgDeleteActionFailed()
	{
            $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/delete');
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		
		$this->reset();
		$organisation = $this->createOrganisation();
		$this->mockDatabaseException('Org');
                $this->getRequest()->getQuery()->fromArray($this->getValidServerKey());
		$this->dispatch('/api/org/delete', 'GET', array(
			'organisation_id' => $organisation->getOrganisationId(),
			));
		$contents = $this->getResponseObjectFromJson();
		$this->assertEquals($contents->result, 0);
		$this->assertContains($contents->message, self::DATABASE_EXCEPTION_MESSAGE);
	}
    
    public function testGetListOrg()
    {
        $org = $this->createOrganisation();
        $this->dispatch('/api/org/get-list', 'GET', $this->getValidServerKey());
        $this->assertResponseIsJson();
        $this->assertContains($org->getTitle(), $this->getResponse()->getBody());
    }
}
