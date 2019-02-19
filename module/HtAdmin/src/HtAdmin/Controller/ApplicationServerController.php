<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Where;
use Zend\View\Model\JsonModel;


class ApplicationServerController extends AbstractActionController
{
    public function serversAction()
    {
        $appId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        /* @var $server \HtAuthentication\Model\Application */
        $app = $sl->get('App');
        if (!$app->load($appId)) {
            return $this->gotoAppPage($this->translate('No application found.'));
        }
        
        /* @var $appServerCollection \HtAuthentication\Model\ApplicationServerCollection */
        $appServerCollection = $sl->get('AppServerCollection');
        
        $totalServers = $appServerCollection->count(array('application_id' => $appId));
        $pagingParamsData = $this->pagingParams()->get($totalServers);
        
        $servers = $appServerCollection->getAppServer(array('application_id' => $appId), $pagingParamsData['offset'], $pagingParamsData['limit']);
        
        $paginator = new Paginator(new PaginatorNull($totalServers));
        $paginator->setItemCountPerPage($pagingParamsData['limit']);
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
            'servers' => $servers,
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
            'totalServers' => $totalServers,
            'application' => $app,
            'paginator' => $paginator,
        );
    }
    
    protected function gotoAppPage($message = null, $error = true)
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        
        return $this->redirect()->toRoute('admin/app');
    }
    
    /**
     * use for add-server action
     */
    public function getServerAction()
    {
        $appId = $this->params()->fromRoute('id');
        $keyword = $this->params()->fromQuery('keyword');
        $page = $this->params()->fromQuery('page', 1);
		$limit = $this->params()->fromQuery('page_limit', 10);
        
        $sl = $this->getServiceLocator();
        
        /* @var $appServerColletion \HtAuthentication\Model\ApplicationServer */
        $appServerColletion = $sl->get('AppServer');
        
        $rowset = $appServerColletion->getAll(array('application_id' => $appId));
        $serverIds = array();
        foreach ($rowset as $row) {
            $serverIds[] = $row->getServerId();
        }
        
        $where = new Where();
        $where->like('title', "%{$keyword}%");
        if ($serverIds) {
            /* remove servers already is server of application */
            $where->addPredicate(new NotIn('server_id', $serverIds));
        }
        
        
		$offset = ($page - 1) * $limit;
        /* @var $server \HtAuthentication\Model\Server */
        $server = $sl->get('Server');
        
        $totalServers = $server->count($where);
        $servers = $server->getAll($where, $offset, $limit)->toArray();
        
        return new JsonModel(array(
            'total' => $totalServers,
            'servers' => $servers,
        ));
    }
    
    public function addServerAction()
    {
        $sl = $this->getServiceLocator();
		
		$appId = $this->params()->fromRoute('id');
		
        /* @var $app \HtAuthentication\Model\Application */
		$app = $sl->get('App');
		if (!$app->load($appId)) {
			return $this->gotoAppPage($this->translate('Application not found.'));
		}
		
		if ($this->getRequest()->isPost()) {
			$serverId = $this->params()->fromPost('server_id');
		} else {
			return $this->gotoAppServer($appId);
		}
		
		$server = $sl->get('Server');
		if (!$server->load($serverId)) {
			return $this->gotoAppServer($appId, sprintf($this->translate('Not found server with id: %s'), $serverId));
		}
		
		if ($app->hasServer($server)) {
			return $this->gotoAppServer(
                    $appId,
                    sprintf($this->translate('<b>%s</b> is already has <b>%s</b> server.'), $app->getTitle(), $server->getDomain())
                );
		}
		
		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
            $app->addServer($server);
			$connection->commit();
			return $this->gotoAppServer($appId, $this->translate('Add server successfully.'), false);
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}
    }
    
    protected function gotoAppServer($appId, $message = null, $error = true)
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        return $this->redirect()->toRoute('admin/app/server', array('id' => $appId));
    }
    
    public function deleteServerAction()
    {
        $appServerId = $this->params()->fromRoute('id');
        
        $sl = $this->getServiceLocator();
        /* @var $appServer \HtAuthentication\Model\ApplicationServer */
        $appServer = $sl->get('AppServer');
        if (!$appServer->load($appServerId)) {
            return $this->gotoAppPage($this->translate('An error occurred. Please try again later.'));
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $appServer->delete();
            $connection->commit();
            return $this->gotoAppServer($appServer->getApplicationId(), $this->translate('Delete server successfully'), false);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
}
