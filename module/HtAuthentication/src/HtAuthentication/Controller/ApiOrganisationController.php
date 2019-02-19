<?php

namespace HtAuthentication\Controller;

use HtApplication\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Mvc\MvcEvent;

class ApiOrganisationController extends AbstractActionController
{
	/**
	 * Validate if a secret is correct
	 * 
	 * @param string $secret
	 * @return boolean
	 */
	protected function validServerSecret($secret)
	{
		//check secret valid
		$sl = $this->getServiceLocator();
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		$serverObject = $server->getBySecret($secret);
		if (!$serverObject) {
			return false;
		} else {
			return true;
		}
	}
	
	protected function getInputFilterForCreate()
	{
		$inputFilter = $this->getInputFilterForServer();
		$factory = new InputFactory();
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_name',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			)
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_description',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			)
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_domain',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			)
		)));
		
		return $inputFilter;
	}
	
	protected function getInputFilterForServer(InputFilter $inputFilter = null, InputFactory $factory = null)
	{
		if ($inputFilter === null) {
			$inputFilter = new InputFilter();
		}
		
		if ($factory === null) {
			$factory = new InputFactory();
		}
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
				
		$inputFilter->add($factory->createInput(array(
			'name' => 'WS_server_ip',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
			),
		)));
		
		return $inputFilter;
	}
	
	protected function getInputFilterForUpdate()
	{
		$inputFilter = $this->getInputFilterForCreate();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			)
		)));
		
		return $inputFilter;
	}
	
	protected function getInputFilterForExist()
	{
		$inputFilter = $this->getInputFilterForCreate();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_id',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			)
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_name',
			'required' => false,
			'filters' => array(
				array('name' => 'StringTrim'),
				array('name' => 'StripTags'),
			)
		)));
		
		return $inputFilter;
	}

	protected function getInputFilterForDelete()
	{
		$inputFilter = $this->getInputFilterForServer();
		
		$factory = new InputFactory();
		$inputFilter->add($factory->createInput(array(
			'name' => 'organisation_id',
			'required' => true,
			'fitlers' => array(
				array('name' => 'StringTrim')
			),
		)));
		return $inputFilter;
	}
	
	public function createAction()
	{
		$request = $this->getRequest();
		/* @var $request \Zend\Http\Request */
		
		if ($request->isPost()) {
			$data = $request->getPost();
		} else {
			$data = $request->getQuery();
		}
		
		$filter = $this->getInputFilterForCreate();
		$filter->setData($data);
		
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		$data = array(
			'title' => $filter->getValue('organisation_name'),
			'description' => $filter->getValue('organisation_description') ?: '',
			'domain' => $filter->getValue('organisation_domain') ?: '',
		);
		
		$sl = $this->getServiceLocator();
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$organisation->exchangeArray($data);
			$organisation->save();
			$connection->commit();
			return new JsonModel(array(
				'result' => 1,
				'message' => 'Create organisation successfully.'
			));
		} catch (\Exception $e) {
			$connection->rollback();
			return new JsonModel(array(
				'result' => 0,
				'message' => $e->getMessage()
			));
		}
	}
	
	protected function error($message)
	{
		return new JsonModel(array(
			'result' => 0,
			'message' => $message
		));
	}
	
	public function updateAction()
	{
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$data = $request->getPost();
		} else {
			$data = $request->getQuery();
		}
		
		$filter = $this->getInputFilterForUpdate();
		$filter->setData($data);
		
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		if (!$this->validServerSecret($filter->getValue('WS_server_id'))) {
			return $this->error('Invalid server id.');
		}
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $this->getServiceLocator()->get('Org');
		
		if (!$organisation->load($filter->getValue('organisation_id'))) {
			return new JsonModel(array(
				'result' => 0,
				'message' => 'Organisation does not exist.'
			));
		}
		
		$data = array(
			'organisation_id' => $filter->getValue('organisation_id'),
			'title' => $filter->getValue('organisation_name'),
			'description' => $filter->getValue('organisation_description') ?: '',
			'domain' => $filter->getValue('organisation_domain') ?: '',
		);
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$organisation->exchangeArray($data);
			$organisation->save();
			$connection->commit();
			return new JsonModel(array(
				'result' => 1,
				'message' => 'Update organisation info successfully.'
			));
		} catch (\Exception $e) {
			$connection->rollback();
			return new JsonModel(array(
				'result' => 0,
				'message' => $e->getMessage(),
			));
		}
		
	}
	
	public function existAction()
	{
		$filter = $this->getInputFilterForExist();
		$filter->setData($this->getRequest()->getQuery());
		
		$organisationId = $filter->getValue('organisation_id');
		$organisationName = $filter->getValue('organisation_name');
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $this->getServiceLocator()->get('Org');
		if ($organisationId && !empty($organisationName)) {
			if ($organisation->count(array('title' => $organisationName, 'organisation_id' => $organisationId))) {
				return new JsonModel(array(
					'result' => 1,
				));
			}
		}
		
		if ($organisation->load($organisationId)) {
			return new JsonModel(array(
				'result' => 1,
			));
		}
		
		if ($organisation->count(array('title' => $organisationName))) {
			return new JsonModel(array(
				'result' => 1,
			));
		}
		
		return new JsonModel(array(
			'result' => 0,
			'message' => 'Organisation does not exsit.',
		));
	}
	
	public function deleteAction()
	{
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $request->getPost();
		} else {
			$data = $request->getQuery();
		}
		
		$filter = $this->getInputFilterForDelete();
		$filter->setData($data);
		if (!$filter->isValid()) {
			return $this->error($filter->getMessages());
		}
		
		if (!$this->validServerSecret($filter->getValue('WS_server_id'))) {
			return $this->error('Invalid server id.');
		}
		
		$organisationId = $filter->getValue('organisation_id');
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $this->getServiceLocator()->get('Org');
		if (!$organisation->load($organisationId)) {
			return new JsonModel(array(
				'result' => 0,
				'message' => 'Organisation does not exsit.',
			));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$organisation->delete();
			$connection->commit();
			return new JsonModel(array(
				'result' => 1
			));
		} catch (\Exception $e) {
			$connection->rollback();
			return new JsonModel(array(
				'result' => 0,
				'message' => $e->getMessage()
			));
		}
	}
        
    public function onDispatch(MvcEvent $e)
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
        } else {
            $data = $request->getQuery();
        }
        
        $filter = $this->getInputFilterForServer();
        $filter->setData($data);

        if (!$filter->isValid()) {
            return $e->setResult($this->error($filter->getMessages()));
        }

        if (!$this->validServerSecret($filter->getValue('WS_server_id'))) {
            return $e->setResult($this->error('Invalid server id.'));
        }
        
        return parent::onDispatch($e);
    }
    
    public function getListAction()
    {
        /* @var $organisation \HtAuthentication\Model\Organisation */
        $organisation = $this->getServiceLocator()->get('Org');
        $organisations = $organisation->getAll()->toArray();
        
        return new JsonModel(
            $organisations
        );
    }
    
    public function getUsersAction()
    {
        $orgId = $this->params()->fromQuery('org_id');
        if (!$orgId) {
            return $this->error($this->translate('Organisation is required.'));
        }
        $sl = $this->getServiceLocator();
        $org = $sl->get('Org');
        $org->load($orgId);
        $users = $org->getUsers();
        
        return new JsonModel($users->toArray());
    }
}