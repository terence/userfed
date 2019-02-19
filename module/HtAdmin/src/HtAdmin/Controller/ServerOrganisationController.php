<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use Zend\View\Model\ViewModel;

class ServerOrganisationController extends AbstractActionController
{
    public function viewServerAction()
    {
        $serverId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        /* @var $server \HtAuthentication\Model\Server */
        $server = $sl->get('Server');
        if (!$server->load($serverId)) {
            return $this->gotoListServer($this->translate('No server found.'));
        }
        
        /* @var $appServerOrg \HtAuthentication\Model\ApplicationServerOrganisationCollection */
        $appServerOrg = $sl->get('AppServerOrgCollection');
        $totalOrgs = $appServerOrg->count(array('server_id' => $serverId));
        $pagingParams = $this->pagingParams()->get($totalOrgs);
        
        $paginator = new Paginator(new PaginatorNull($totalOrgs));
        $paginator->setCurrentPageNumber($pagingParams['page']);
        $paginator->setItemCountPerPage($pagingParams['limit']);
        
        $organisations = $appServerOrg->getOrgByServerId($serverId, $pagingParams['offset'], $pagingParams['limit']);
        return array(
            'organisations' => $organisations,
            'totalOrgs' => $totalOrgs,
            'paginator' => $paginator,
            'server' => $server,
        );
    }
    
    public function addOrgAction()
    {
        
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
