<?php

namespace HtAdmin\Controller;
use HtApplication\Controller\AbstractActionController;
use HtAdmin\Form\Application As ApplicationForm;
use HtAdmin\Form\Filter\Application as AppFilterForm;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\Db\Sql\Select;

class ApplicationController extends AbstractActionController
{
	public function indexAction()
	{
		$sl = $this->getServiceLocator();
		
		$filterForm = new AppFilterForm();
		$filterForm->setData($this->params()->fromQuery());
		$title = $this->params()->fromQuery('keyword');
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		$where = function (Select $select) use ($title) {
			$select->where->like('title', "%{$title}%");
		};
		
		
		$totalApplications = $application->count($where);
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalApplications);
		
		$applications = $application->getAll($where, $pagingParamsData['offset'], $pagingParamsData['limit']);
		$applications->buffer();
		
		$applicationIds = array();
		foreach ($applications as $application) {
			$applicationIds[] = $application->getApplicationId();
		}
		$countOrgs = array();
		$countUsers = array();
		if ($applicationIds) {
			/* @var $appServerOrgCollection \HtAuthentication\Model\ApplicationServerOrganisationCollection */
			$appServerOrgCollection = $sl->get('AppServerOrgCollection');

			$rowset = $appServerOrgCollection->countOrg(array('application_id' => $applicationIds), 'application_id');
			foreach ($rowset as $row) {
				$countOrgs[$row->application_id] = $row->count_orgs;
			}
			
			/* @var $userAppCollection \HtAuthentication\Model\UserApplicationCollection */
			$userAppCollection = $sl->get('UserAppCollection');
			$rowset = $userAppCollection->countUser(array('application_id' => $applicationIds), 'application_id');
			foreach ($rowset as $row) {
				$countUsers[$row->application_id] = $row->count_users;
			}
		}
		
		$paginator = new Paginator(new PaginatorNull($totalApplications));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		
		return array(
			'applications' => $applications,
			'paginator' => $paginator,
			'countOrgs' => $countOrgs,
			'countUsers' => $countUsers,
			'filterForm' => $filterForm,
		);
			
	}
	
	public function createAction()
	{
		$sl = $this->getServiceLocator();
		$form = new ApplicationForm();
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			/* @var $application \HtAuthentication\Model\Application */
			$application = $sl->get('App');
			
			$data = $request->getPost();
			$form->setData($data);
			$form->setInputFilter($application->getInputFilter());
			
			if ($form->isValid()) {
				$data = $form->getData();
				$application->exchangeArray($data);
				
				$connection = $this->getConnection();
				try {
					$connection->beginTransaction();
					$application->save();
					$connection->commit();
					
					return $this->gotoListPage($this->translate('Add application successfully.'), false);
				} catch(\Exception $e) {
					$connection->rollback();
					throw $e;
				}
			}
			
		}
		
		return array(
			'form' => $form
		);
	}
	
	public function editAction()
	{
		$sl = $this->getServiceLocator();
		$applicationId = $this->params()->fromRoute('id');
		
		$form = new ApplicationForm();
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		if (!$application->load($applicationId)) {
			return $this->gotoListPage(sprintf($this->translate('Not found application with id: %s'), $applicationId));
		}
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$data = $request->getPost();
			$form->setData($data);
			$form->setInputFilter($application->getInputFilter());
			if ($form->isValid()) {
				$data = $form->getData();
				$application->exchangeArray($data);
				
				$connection = $this->getConnection();
				try {
					$connection->beginTransaction();
					$application->save();
					$connection->commit();
					return $this->gotoListPage($this->translate('Update application info successfully.'), false);
				} catch(\Exception $e) {
					$connection->rollback();
					throw $e;
				}
			}
		} else {
			$form->setData($application->toArray());
		}
		
		return array(
			'form' => $form,
			'application' => $application
		);
				
	}
	
	public function deleteAction()
	{
		$applicationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		if (!$application->load($applicationId)) {
			return $this->gotoListPage(sprintf($this->translate('Not found application with id: %s'), $applicationId));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$application->delete();
			$connection->commit();
			return $this->gotoListPage($this->translate('Delete application successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		
	}
	
	/**
	 * Add message to flashMessager and redirect to list application page.
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoListPage($message, $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		
		return $this->redirect()->toRoute('admin/app');
	}
}
