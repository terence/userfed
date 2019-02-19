<?php
namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Predicate\NotIn;

class UserOrganisationController extends AbstractActionController
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
				
		/* @var $userOrganisatnion \HtAuthentication\Model\UserOrganisationCollection */
		$userOrganisatnion = $sl->get('UserOrgCollection');
		
		$totalUsers = $userOrganisatnion->count(array('organisation_id' => $organisationId));
		
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalUsers);
		
		$users = $userOrganisatnion->getOrgUsers(array('organisation_id' => $organisationId),
				$pagingParamsData['offset'],
				$pagingParamsData['limit']);
		
				
		$paginator = new Paginator(new PaginatorNull($totalUsers));
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
			'users' => $users,
			'totalUsers' => $totalUsers,
			'paginator' => $paginator,
			'organisation' => $organisation,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage
		);
		
	}
	
	public function addUserAction()
	{
		$organisationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		if ($this->getRequest()->isPost()) {
			$userId = $this->params()->fromPost('user_id');
		} else {
			return $this->gotoListUserPage($organisationId);
		}
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		
		/* redirect to organisation list page. */
		if (!$organisation->load($organisationId)) {
			$this->flashMessenger()->addErrorMessage(sprintf($this->translate('Not found organisation with id: %s'), $organisationId));
			return $this->redirect()->toRoute('admin/org');
		}
		
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		
		if (!$user->load($userId)) {
			return $this->gotoListUserPage($organisationId, sprintf($this->translate('Not found user with id %s'), $userId));
		}
		
		/* @var $userOrganisation \HtAuthentication\Model\UserOrganisation */
		$userOrganisation = $sl->get('UserOrg');
		
		$userOrganisation->setUserId($userId);
		
		if ($userOrganisation->isAdded($organisation)) {
			return $this->gotoListUserPage($organisationId, $this->translate('This user already is member of organisation.'));
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$organisation->addUser($user);
			$connection->commit();
			return $this->gotoListUserPage($organisationId, $this->translate('Add user successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	public function deleteUserAction()
	{
		$userOrganisationId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $userOrganisation \HtAuthentication\Model\UserOrganisation */
		$userOrganisation = $sl->get('UserOrg');
		
		if (!$userOrganisation->load($userOrganisationId)) {
			$this->flashMessenger()->addErrorMessage(sprintf($this->translate('Not found id %s'), $userOrganisationId));
			return $this->redirect()->toRoute('admin/org');
		}
		
		$organisationId = $userOrganisation->getOrganisationId();
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$userOrganisation->delete();
			$connection->commit();
			return $this->gotoListUserPage($organisationId, $this->translate('User has been remove from organisation.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	/**
	 * Add message to flashMessager and redirect to view list user of organisation.
	 * @param int $organisationId
	 * @param string $message
	 * @param boolean $error
	 * @return \Zend\Http\Response
	 */
	protected function gotoListUserPage($organisationId, $message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('admin/org/user', array('id' => $organisationId));
	}
	
	/**
	 * Get user for XmlHttpRequest
	 * @return JsonModel
	 */
	/**
	 * get user for XmlHttpRequest
	 * @return JsonModel
	 */
	public function getUserAction()
	{
		$search =  $this->params()->fromQuery('search');
		$limit = (int) $this->params()->fromQuery('page_limit', 10);
		$page = $this->params()->fromQuery('page', 1);
		$organisationId = $this->params()->fromRoute('id');
		$offset = ($page - 1) * $limit;
				
		$sl = $this->getServiceLocator();
		
		/* @var $userOrgCollection \HtAuthentication\Model\UserOrganisationCollection */
		$userOrgCollection = $sl->get('UserOrgCollection');
		
		
		$totalUsers = $userOrgCollection->countForAjax($organisationId, $search);
		
		$results = $userOrgCollection->getUsersForAjax($organisationId, $search, $offset, $limit);
		
		$users = $results->toArray();
		return new JsonModel(array(
			'total' => $totalUsers,
			'users' => $users
		));
	}
	
	/**
	 * Get organisation by userId for XmlHttpRequest.
	 * @return JsonModel
	 */
	public function getOrganisationAction()
	{
		$userId = $this->params()->fromRoute('id');
		
		$sl = $this->getServiceLocator();
		
		/* @var $userOrgCollection \HtAuthentication\Model\UserOrganisationCollection */
		$userOrgCollection = $sl->get('UserOrgCollection');
		
		$organisation = $userOrgCollection->getOrganisationForAjax($userId);
		
		return new JsonModel(array(
			'organisations' => $organisation->toArray()
		));
	}
	
	public function viewUserAction()
	{
		$userId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		
		if (!$user->load($userId)) {
			return $this->gotoUserPage($this->translate('No user found.'));
		}
		
		/* @var $userOrg \HtAuthentication\Model\UserOrganisationCollection */
		$userOrg = $sl->get('UserOrgCollection');
		$totalUserOrgs = $userOrg->count(array('user_id' => $userId));
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalUserOrgs);
		$userOrgs = $userOrg->getUserOrgs($user, $pagingParamsData['limit'], $pagingParamsData['offset']);
		$rowset = $userOrg->getAll(array('user_id' => $userId));
		$orgIds = array();
		foreach ($rowset as $row) {
			$orgIds[] = $row->getOrganisationId();
		}
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		$notIn = array();
		if (!empty($orgIds)) {
			$notIn = new NotIn('organisation_id', $orgIds);
		}
		
		$paginator = new Paginator(new PaginatorNull($totalUserOrgs));
		$paginator->setItemCountPerPage($pagingParamsData['limit']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		
		$organisations = $organisation->getAll($notIn);
		
		$errorMessage = null;
		if ($this->flashMessenger()->hasErrorMessages()) {
			$errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
		}
		
		$successMessage = null;
		if ($this->flashMessenger()->hasSuccessMessages()) {
			$successMessage = implode('<br />', $this->flashMessenger()->getSuccessMessages());
		}
		
		return array(
			'user' => $user,
			'userOrgs' => $userOrgs,
			'paginator' => $paginator,
			'organisations' => $organisations,
			'errorMessage' => $errorMessage,
			'successMessage' => $successMessage,
		);
	}
	
	protected function gotoUserPage($message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('admin/user');
	}
	
	protected function gotoOrgListPage($userId, $message = '', $error = true)
	{
		if ($error) {
			$this->flashMessenger()->addErrorMessage($message);
		} else {
			$this->flashMessenger()->addSuccessMessage($message);
		}
		return $this->redirect()->toRoute('admin/user/org', array('id' => $userId));
	}

	public function addOrgAction()
	{
		$userId = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		if (!$user->load($userId)) {
			return $this->gotoOrgListPage($userId, $this->translate('No user found.'));
		}
		
		$orgId = $this->params()->fromPost('organisation_id');
		$organisation = $sl->get('Org');
		if (!$organisation->load($orgId)) {
			return $this->gotoOrgListPage($userId, $this->translate('No organisation found.'));
		}
		
		/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
		$userOrg = $sl->get('UserOrg');
		$userOrg->setUserId($userId);
		if ($userOrg->isAdded($organisation)) {
			return $this->gotoOrgListPage($userId, $this->translate('This user already is member of organisation'));
		}
		try {
			$connection = $this->getConnection();
			$connection->beginTransaction();
			$userOrg->addOrgToUser($organisation);
			$connection->commit();
			return $this->gotoOrgListPage($userId, $this->translate('Add organisation for user successfully'), false);
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
	
	public function deleteOrgAction()
	{
		$id = $this->params()->fromRoute('id');
		$sl = $this->getServiceLocator();
		
		/* @var $userOrg \HtAuthentication\Model\UserOrganisation */
		$userOrg = $sl->get('UserOrg');
		if (!$userOrg->load($id)) {
			return $this->gotoUserPage($this->translate("User isn't member of organisation."));
		}
		
		$userId = $userOrg->getUserId();
		try {
			$connection = $this->getConnection();
			$connection->beginTransaction();
			$userOrg->delete();
			$connection->commit();
			return $this->gotoOrgListPage($userId, $this->translate('Delete organisation successfully'), false);
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}
}