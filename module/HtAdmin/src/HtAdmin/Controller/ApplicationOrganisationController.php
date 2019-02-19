<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Select;

class ApplicationOrganisationController extends AbstractActionController
{
	public function viewOrgAction()
	{
		$organisationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		
		if (!$organisation->load($organisationId)) {
			$this->flashMessenger()->addErrorMessage(sprintf($this->translate('Not found organisation with id: %s'), $organisationId));
			return $this->redirect()->toRoute('admin/org');
		}
		
		/* @var $appOrgServerCollection \HtAuthentication\Model\ApplicationServerOrganisationCollection */
		$appOrgServerCollection = $sl->get('AppServerOrgCollection');
		
		$totalApps = $appOrgServerCollection->count(array('organisation_id' => $organisationId));
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalApps);
		
		$apps = $appOrgServerCollection->getByOrganisationId($organisationId, $pagingParamsData['offset'], $pagingParamsData['limit']);
		
		$paginator = new Paginator(new PaginatorNull($totalApps));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		
		$errorMessage = '';
		$successMessage = '';
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMessage = implode("<br />", $this->flashMessenger()->getSuccessMessages());
		}
		
		if ($this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode("<br />", $this->flashMessenger()->getErrorMessages());
		}
				
		return array(
			'apps' => $apps,
			'organisation' => $organisation,
			'paginator' => $paginator,
			'totalApps' => $totalApps,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage
		);
	}
	
	/**
	 * Add message to flashMessager and redirect to list organisation of application page
	 * @param int $orgId
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoListOrgApp($appId, $message = '', $error = true)
	{
		if (!empty($message)) {
			$this->addMessageToFlash($message, $error);
		}
		return $this->redirect()->toRoute('admin/app/org', array('id' => $appId));
	}
	
	/**
	 * Add message to flashMessager
	 * @param string $message
	 * @param boolean $error
	 */
	protected function addMessageToFlash($message, $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
	}

	public function addApplicationAction()
	{
		$organisationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		if (!$this->getRequest()->isPost()) {
			return $this->gotoListAppOrgPage($organisationId);
		}
		
		$applicationId = $this->params()->fromPost('application_id');
		$serverId = $this->params()->fromPost('server_id');
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		if (!$organisation->load($organisationId)) {
			$this->addMessageToFlash(sprintf($this->translate('Not found organisation with id: %s'), $organisationId));
			return $this->redirect()->toRoute('admin/org');
		}
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		if (!$application->load($applicationId)) {
			return $this->gotoListAppOrgPage($organisationId ,sprintf($this->translate('Not found application with id: %s'), $applicationId));
		}
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		if (!$server->load($serverId)) {
			return $this->gotoListAppOrgPage($organisationId, sprintf($this->translate('Not found server with id: %s'), $serverId));
		}
		
		if (!$application->hasServer($server)) {
			return $this->gotoListAppOrgPage(
					$organisationId, 
					sprintf($this->translate('<b>%s</b> has not <b>%s</b> server.'), $application->getTitle(), $server->getDomain())
			);
		}
		
		if ($organisation->hasAccess($application, $server)) {
			return $this->gotoListAppOrgPage($organisationId,
					$this->translate('This organisation already has permission access this server.'));
		}
		
		$connection  = $this->getConnection();
		try {
			$connection->beginTransaction();
			$application->addAccess($organisation, $server);
			$connection->commit();
			return $this->gotoListAppOrgPage($organisationId, $this->translate('Add application for ogranisation successfully.'), false);
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	/**
	 * Add message to flashMessager and redirect to list application of organisation page
	 * @param int $orgId
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoListAppOrgPage($orgId, $message = '', $error = true)
	{
		if (!empty($message)) {
			$this->addMessageToFlash($message, $error);
		}
		return $this->redirect()->toRoute('admin/org/app', array('id' => $orgId));
	}
	
	public function deleteApplicationAction()
	{
		$appServerOrgId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $appSeverOrg \HtAuthentication\Model\ApplicationServerOrganisation */
		$appSeverOrg = $sl->get('AppServerOrg');
		
		if (!$appSeverOrg->load($appServerOrgId)) {
			$this->addMessageToFlash(
					sprintf($this->translate("Cannot found application server organisation with id %s"), $appServerOrgId)
			);
			return $this->redirect()->toRoute('admin/org');
		}
		
		$organisationId = $appSeverOrg->getOrganisationId();
		
		$connection = $this->getConnection();
		
		try {
			$connection->beginTransaction();
			$appSeverOrg->delete();
			$connection->commit();
			return $this->gotoListAppOrgPage($organisationId, $this->translate('Delete application successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	/**
	 * Get list server by organisationId and applicationId for XmlHttpRequest.
	 * URI request: '/admin/app-org/get-server-org/:applicationId?org-id=organisationId'
	 * @return JsonModel 
	 */
	public function getServerOrgAction()
	{
		$organisationId = $this->params()->fromQuery('org-id');
		$applicationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $appSeverOrgCollection \HtAuthentication\Model\ApplicationServerOrganisationCollection */
		$appSeverOrgCollection = $sl->get('AppServerOrgCollection');
		
		$result = $appSeverOrgCollection->getServerOrg(array(
			'organisation_id' => $organisationId,
			'application_id' => $applicationId
		));
		
		$servers = $result->toArray();
		
		return new JsonModel(array(
			'servers' => $servers 
		));
	}
	
	/**
	 * get list server subtract servers already has permission access.
	 * URI request: '/admin/app-org/get-server/:applicationId?org-id=organisationId'
	 * @return JsonModel
	 */
	public function getServerAction()
	{
		$applicationId = $this->params()->fromRoute('id');
		$organisationId = $this->params()->fromQuery('org-id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisation */
		$appServerOrg = $sl->get('AppServerOrg');
		
		$rowset = $appServerOrg->getAll(array('application_id' => $applicationId, 'organisation_id' => $organisationId));
		
		/**
		 * Get list server, organistion can used.
		 */
		$serverIds = array();
		foreach ($rowset as $row) {
			$serverIds[] = $row->getServerId();
		}
		
		/* @var $applicationSeverCollection \HtAuthentication\Model\ApplicationServerCollection */
		$applicationSeverCollection = $sl->get('AppServerCollection');
		
		$rowset = $applicationSeverCollection->getServerByApplicationId($applicationId, $serverIds);
		
		return new JsonModel(array(
			'servers' => $rowset->toArray()
		));
	}
	
	/**
	 * Add message into flashMessager and redirect to list application page.
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoListApp($message = '', $error = true)
	{
		if (!empty($message)) {
			$this->addMessageToFlash($message, $error);
		}
		return $this->redirect()->toRoute('admin/app');
	}
	
	/**
	 * Allow an organion access into application.
	 * @return \Zend\Http\Response
	 */
	public function addOrgAction()
	{
		$applicationId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$organisationId = $this->params()->fromPost('organisation_id');
			$serverId = $this->params()->fromPost('server_id');
		} else {
			return $this->gotoListOrgApp($applicationId);
		}
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		if (!$application->load($applicationId)) {
			return $this->gotoListApp(sprintf($this->translate('Not found application with id: %s'), $applicationId));
		}
		
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		if (!$organisation->load($organisationId)) {
			return $this->gotoListOrgApp($applicationId, sprintf($this->translate('Not found organisation with id: %s'), $organisationId));
		}
		
		/* @var $server \HtAuthentication\Model\Server */
		$server = $sl->get('Server');
		if (!$server->load($serverId)) {
			return $this->gotoListOrgApp($applicationId, sprintf($this->translate('Not found server with id: %s'), $serverId));
		}
		
		if (!$application->hasServer($server)) {
			return $this->gotoListOrgApp(
					$applicationId, 
					sprintf($this->translate('<b>%s</b> has not <b>%s</b> server.'), $application->getTitle(), $server->getDomain())
			);
		}
		
		if ($organisation->hasAccess($application, $server)) {
			return $this->gotoListAppOrgPage($organisationId,
					$this->translate('This organisation already has permission access this server.'));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$application->addAccess($organisation, $server);
			$connection->commit();
			return $this->gotoListOrgApp($applicationId, $this->translate('Add organisation successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
		
	}
	
	/**
	 * View list Organisation use Application
	 * @return \Zend\View\Model\ViewModel
	 */
	public function viewAppAction()
	{
		$applicationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		if (!$application->load($applicationId)) {
			return $this->gotoListApp(
					sprintf($this->translate('Not found application with id: %s'),
					$applicationId
			));
		}
		
		
		/* @var $appServerOrgCollection \HtAuthentication\Model\ApplicationServerOrganisationCollection */
		$appServerOrgCollection = $sl->get('AppServerOrgCollection');
		
		$totalOrgs = $appServerOrgCollection->count(array(
			'application_id' => $applicationId,
		));
		
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalOrgs);
		
		$organisations = $appServerOrgCollection->getByApplicationId($applicationId, $pagingParamsData['offset'], $pagingParamsData['limit']);
		
		$paginator = new Paginator(new PaginatorNull($totalOrgs));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		
		$errorMessage = '';
		if ($this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
		}
		
		$successMesage = '';
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMesage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
		}
		
		return array(
			'organisations' => $organisations,
			'application' => $application,
			'totalOrgs' => $totalOrgs,
			'paginator' => $paginator,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMesage
		);
	}
	
	public function deleteOrgAction()
	{
		$appServerOrgId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		
		/* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisation */
		$appServerOrg = $sl->get('AppServerOrg');
		
		if (!$appServerOrg->load($appServerOrgId)) {
			return $this->gotoListApp();
		}
		
		$applicationId = $appServerOrg->getApplicationId();
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$appServerOrg->delete();
			$connection->commit();
			return $this->gotoListOrgApp($applicationId, $this->translate('Delete organisation successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	/**
	 * Get list application for XmlHttpRequest
	 * @return \Zend\View\Model\JsonModel
	 */
	public function getApplicationAction()
	{
		$key = $this->params()->fromQuery('key');
		$page = $this->params()->fromQuery('page', 1);
		$limit = $this->params()->fromQuery('page_limit', 10);
		
		$offset = ($page - 1) * $limit;
		$sl = $this->getServiceLocator();
		
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		
		$where = function (Select $select) use ($key) {
			$select->where->like('title', "%{$key}%");
		};
		
		$total = $application->count($where);
		
		$results = $application->getAll($where, $offset, $limit);
		$apps = $results->toArray();
		
		return new JsonModel(array(
			'apps' => $apps,
			'total' => $total
		));
	}
	
}
