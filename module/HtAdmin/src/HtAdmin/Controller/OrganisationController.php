<?php
namespace HtAdmin\Controller;
use HtApplication\Controller\AbstractActionController;
use HtAdmin\Form\Filter\Organisation as OrgFilterForm;
use HtAdmin\Form\Organisation as OrganisationForm ;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\Db\Sql\Select;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Predicate\NotIn;

class OrganisationController extends AbstractActionController
{
	/**
	 * show list Organisation.
	 */
	public function indexAction()
	{
		$sl = $this->getServiceLocator();
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		
		$filterForm = new OrgFilterForm();
		$title = $this->params()->fromQuery('keyword');
		$filterForm->setData($this->params()->fromQuery());
		
		$where = function (Select $select) use ($title){
			$select->where->like('title', "%$title%");
		};
		
		$totalOrganisation = $organisation->count($where);
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalOrganisation);
		
		
		$listOrganisation = $organisation->getAll($where, $pagingParamsData['offset'], $pagingParamsData['limit']);
		$listOrganisation->buffer();
		$orgIds = array();
		foreach ($listOrganisation as $org) {
			$orgIds[] = $org->getOrganisationId();
		}
		$countApplications = array();
		$countUsers = array();
		if ($orgIds) {
			/* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisationCollection */
			$appServerOrg = $sl->get('AppServerOrgCollection');
			$results = $appServerOrg->countApplication($orgIds);
			foreach ($results as $count) {
				$countApplications[$count['organisation_id']] = $count['count_apps'];
			}
			
			/* @var $userOrgCollection \HtAuthentication\Model\UserOrganisationCollection */
			$userOrgCollection = $sl->get('UserOrgCollection');
			
			$results = $userOrgCollection->countUser($orgIds);
			foreach ($results as $count) {
				$countUsers[$count['organisation_id']] = $count['count_apps'];
			}
		}
		$paginator = new Paginator(new PaginatorNull($totalOrganisation));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		
		$errorMessage = '';
		$successMessage = '';
		if ($this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
		}
		
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
		}
		
		return array(
			'organisations' => $listOrganisation,
			'paginator' => $paginator,
			'filterForm' => $filterForm,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage,
			'countApplications' => $countApplications,
			'countUsers' => $countUsers,
		);
	}
	
	public function createAction()
	{
		$form = new OrganisationForm();
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $request->getPost();
			$form->setData($data);
			$sl = $this->getServiceLocator();
			
			/* @var $organisation \HtAuthentication\Model\Organisation */
			$organisation = $sl->get('Org');
			$form->setInputFilter($organisation->getInputFilter());
			if ($form->isValid()) {
				$data = $form->getData();
				$connection = $this->getConnection();
				try {
					$connection->beginTransaction();
					$organisation->exchangeArray($data);
					$organisation->setCreationDate(date('Y-m-d H:i:s'));
					$organisation->save();
					$connection->commit();
					$this->gotoListPage($this->translate('Create organisation successsful.'), false);
				} catch(\Exception $e) {
					$connection->rollback();
					throw $e;
				}
			}
		}
		
		return array(
			'form' => $form,
		);
	}
	
	public function editAction()
	{
		$form = new OrganisationForm();
		$organisationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		
		if (!$organisation->load($organisationId)) {
			$message = sprintf($this->translate("No found organisation with id: %s"), $organisationId);
			return $this->gotoListPage($message);
		}
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $request->getPost();
			$form->setData($data);
			$inputFilter = $organisation->getInputFilter();
			$form->setInputFilter($inputFilter);
			if ($form->isValid()) {
				$data = $form->getData();
				$organisation->exchangeArray($data);
				$organisation->setLastUpdated(date('Y-m-d H:i:s'));
				$connection = $this->getConnection();
				try {
					$connection->beginTransaction();
					$organisation->save();
					$connection->commit();
					$this->gotoListPage($this->translate('Update organisation info successfully.'), false);
				} catch(\Exception $e) {
					$connection->rollback();
					throw $e;
				}
			}
		} else {
			$form->setData($organisation->toArray());
		}
		
		return array(
			'form' => $form,
			'organisation' => $organisation
		);
	}
	
	/**
	 * Add message and redirecto to organisation list page.
	 * @param string $message
	 * @param bool $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoListPage($message = '', $error = true)
	{
		if ($message) {
			if ($error) {
				$this->flashMessenger()->addErrorMessage($message);
			} else {
				$this->flashMessenger()->addSuccessMessage($message);
			}
		}
		return $this->redirect()->toRoute('admin/org');
	}
	
	public function deleteAction()
	{
		$organisationId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		
		if (!$organisation->load($organisationId)) {
			$message = sprintf($this->translate("No found organisation with id: %s"), $organisationId);
			return $this->gotoListPage($message);
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$organisation->delete();
			$connection->commit();
			return $this->gotoListPage($this->translate('Delete organisation successfully.'), false);
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	/**
	 * get list server for XmlHttpRequest
	 * @return JsonModel
	 */
	public function getServerAction()
	{
		$appId = $this->params()->fromQuery('application-id');
		$sl = $this->getServiceLocator();
		/* @var $serverCollection \HtAuthentication\Model\ApplicationServerCollection */
		$serverCollection = $sl->get('AppServerCollection');
		
		$rowset = $serverCollection->getServerByApplicationId($appId);
		$servers = $rowset->toArray();
		return new JsonModel(array(
			'servers' => $servers,
		));
	}
	
	/**
	 * get list organisation for XmlHttpRequest
	 * need for add organisation into application
	 * route /admin/organisation/get-org?search=keysearch&page=page_number&page_limit=page_limit
	 * @return \Zend\View\Model\JsonModel
	 */
	public function getOrgAction()
	{
		$page = $this->params()->fromQuery('page', 1);
		$limit = (int) $this->params()->fromQuery('page_limit', 10);
		$search = $this->params()->fromQuery('search');
		
		/**
		 * Only get organistion not has user with user_id equal this params 
		 * If not set will return all.
		 */
		$notHasUser = $this->params()->fromQuery('not_has_user');
		
		$offset = ($page - 1) * $limit;
		
		$sl = $this->getServiceLocator();
		$orgIds = array();
		if ($notHasUser) {
			/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
			$userOrgModel = $sl->get('UserOrg');
			$userOrgs = $userOrgModel->getAll(array('user_id' => $notHasUser));
			foreach ($userOrgs as $userOrg) {
				$orgIds[] = $userOrg->getOrganisationId();
			}
		}
		
		$where = function (Select $select) use ($search, $orgIds) {
			$select->where->like('title', "%{$search}%");
			if (!empty($orgIds)) {
				$predicate = new NotIn('organisation_id', $orgIds);
				$select->where->addPredicate($predicate);
			}
		};
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		
		$total = $organisation->count($where);
		$results = $organisation->getAll($where, $offset, $limit);
		
		$organisations = $results->toArray();
		
		return new JsonModel(array(
			'total' => $total,
			'organisations' => $organisations
		));
	}
}
