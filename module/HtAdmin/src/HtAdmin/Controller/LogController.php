<?php
namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use HtApplication\Model\Log;

class LogController extends AbstractActionController
{
	public function userAction()
	{
		$userId = $this->params()->fromRoute('userId');
		$sl = $this->getServiceLocator();
		$user = $sl->get('User');
		/* @var $user \HtUser\Model\User */
		if (!$user->load($userId)) {
			$this->flashMessenger()->addErrorMessage($this->translate('User does not exist.'));
			return $this->redirect()->toRoute('admin/user', array('action' => 'index'));
		}
		$log = $sl->get('HtApplication\Model\Log');
		/* @var $log \HtApplication\Model\Log */
		$totalLogs = $log->count(array('user_id' => $userId));
		
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalLogs);
		
		$paginator = new Paginator(new PaginatorNull($totalLogs));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
		$userLogs = $log->getAll(array('user_id' => $userId), $pagingParamsData['offset'], $pagingParamsData['limit'], 'timestamp DESC');
		
		return array(
			'userLogs' => $userLogs,
			'count' => $totalLogs,
			'user' => $user,
			'paginator' => $paginator
		);
	}
    
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        
		/* @var $logCollection \HtApplication\Model\LogCollection */
		$logCollection = $sl->get('HtApplication\Model\LogCollection');
		$totalLogs = $logCollection->count();
		
		$pagingParams = $this->pagingParams();
		$pagingParamsData = $pagingParams->get($totalLogs);
		
		$paginator = new Paginator(new PaginatorNull($totalLogs));
		$paginator->setItemCountPerPage($pagingParamsData['itemPerPage']);
		$paginator->setCurrentPageNumber($pagingParamsData['page']);
        $rowset = $logCollection->getLogWithUser(array(), $pagingParamsData['offset'], $pagingParamsData['limit'], 'timestamp DESC');
		$logs = array();
        /* @var $log Log */
        $log = $sl->get('HtApplication\Model\Log');
        
        foreach ($rowset as $row) {
            $name = trim($row->firstname . ' ' . $row->lastname);
            if (!$name) {
                $name = $row->email;
            }
            $logs[] = array(
                'log_id' => $row->log_id,
                'username' => $name,
                'ipAddress'	=> $row->ip_address,
                'time'		=> $log->getTime($row->timestamp),
                'isError'		=> $log->isTypeError($row->type),
                'typename'		=> $log->getTypeName($row->type),
                'message'		=> $row->message,
                );
        }
        
        $errorMessage = '';
        if ($this->flashMessenger()->hasErrorMessages()) {
            $errorMessage = implode('<br />', $this->flashMessenger()->getErrorMessages());
        }
		
		return array(
			'logs' => $logs,
			'count' => $totalLogs,
			'paginator' => $paginator,
            'errorMessage' => $errorMessage,
		);
    }
    
    public function detailsAction()
    {
        $id = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        /* @var $log Log */
        $log = $sl->get('HtApplication\Model\Log');
        if (!$log->load($id)) {
            return $this->gotoIndex($this->translate('Not found log item.'));
        }
        
        $userId = $log->getUserId();
        /* @var $user \HtUser\Model\User */
        $user = $sl->get('User');
        $backUrl = $this->url()->fromRoute('admin/log');
        if ($userId) {
            $user->load($userId);
            $routeMatchName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
            
            if ($routeMatchName == 'admin/user/log/details') {
                /* use view log details from admin/user/log, we should brings user go back correct */
                $backUrl = $this->url()->fromRoute('admin/user/log', array('userId' => $userId));
            }
        }
        
        return array(
            'log' => $log,
            'user' => $user,
            'backUrl' => $backUrl,
        );
        
    }
    
    protected function gotoIndex($message = null, $error = true)
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        return $this->redirect()->toRoute('admin/log');
    }
}
