<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;

class UserServerController extends AbstractActionController
{
    public function userAction()
    {
        $serverId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        $server = $sl->get('Server');
        
        if (!$server->load($serverId)) {
            return $this->gotoListServer($this->translate('No server found.'));
        }
        
        /* @var $userAppCollection \HtAuthentication\Model\UserApplicationCollection */
        $userAppCollection = $sl->get('UserAppCollection');
        $totalUsers = $userAppCollection->count(array('server_id' => $serverId));
        
        $pagingParamsData = $this->pagingParams()->get($totalUsers);
        $users = $userAppCollection->getUserServer($serverId, $pagingParamsData['offset'], $pagingParamsData['limit']);
        
        $paginator = new Paginator(new PaginatorNull($totalUsers));
        $paginator->setCurrentPageNumber($pagingParamsData['page']);
        $paginator->setItemCountPerPage($pagingParamsData['limit']);
        
        
        return array(
            'paginator' => $paginator,
            'users' => $users,
            'totalUsers' => $totalUsers,
            'server' => $server,
        );
    }
    
    protected function gotoListServer($message = null, $error = true)
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        
        return $this->redirect()->toRoute('admin/server');
    }
}
