<?php

namespace HtAdmin\Controller;
use HtApplication\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;
use HtAdmin\Form\Filter\Server as ServerFilterForm;
use Zend\Db\Sql\Where;
use HtAdmin\Form\Server as CreateServerForm;

class ServerController extends AbstractActionController
{
    public function indexAction()
    {
        $keyword = $this->params()->fromQuery('keyword');
        $status = $this->params()->fromQuery('status');
        
        $filterForm = new ServerFilterForm();
        $filterForm->setData($this->params()->fromQuery());
        
        $sl = $this->getServiceLocator();
        /* @var $server \HtAuthentication\Model\Server */
        $server = $sl->get('Server');
        
        $where = new Where();
        $where->like('title', "%$keyword%");

        if ($status) {
            $where->equalTo('status', $status);
        }
        
        $count = $server->count($where);
        $pagingParams = $this->pagingParams()->get($count);
        
        $servers = $server->getAll($where, $pagingParams['offset'], $pagingParams['limit']);
        
        $paginator = new Paginator(new PaginatorNull($count));
        $paginator->setItemCountPerPage($pagingParams['limit']);
        $paginator->setCurrentPageNumber($pagingParams['page']);
        
        $errorMessage = '';
        $successMessage = '';
        $flashMessager = $this->flashMessenger();
        if ($flashMessager->hasErrorMessages()) {
            $errorMessage = implode('<br />', $flashMessager->getErrorMessages());
        }
        
        if ($flashMessager->hasSuccessMessages()) {
            $successMessage = implode('<br />', $flashMessager->getSuccessMessages());
        }
        
        return array(
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'servers' => $servers,
            'paginator' => $paginator,
            'filterForm' => $filterForm,
        );
    }
    
    public function createAction()
    {
        $form = new CreateServerForm();
        
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $sl = $this->getServiceLocator();
            $data = $request->getPost();
            $form->setData($data);
            /* @var $server \HtAuthentication\Model\Server */
            $server = $sl->get('Server');
            $inputFilter = $server->getInputFilter();
            $form->setInputFilter($inputFilter);
            
            if ($form->isValid()) {
                try {
                    $connection = $this->getConnection();
                    $connection->beginTransaction();
                    $data = $form->getData();
                    $server->exchangeArray($data);
                    $server->save();
                    $connection->commit();
                    return $this->gotoIndex($this->translate('Create server successfully.'), false);
                } catch (\Exception $e) {
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
        $serverId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        /* @var $server \HtAuthentication\Model\Server */
        $server = $sl->get('Server');
        if (!$server->load($serverId)) {
            return $this->gotoIndex($this->translate('No server found.'));
        }
                
        $form = new CreateServerForm();
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            
            $inputFilter = $server->getInputFilter();
            $form->setInputFilter($inputFilter);
            
            if ($form->isValid()) {
                try {
                    $connection = $this->getConnection();
                    $connection->beginTransaction();
                    $data = $form->getData();
                    $server->exchangeArray($data);
                    $server->save();
                    $connection->commit();
                    return $this->gotoIndex($this->translate('Update server info successfully.'), false);
                } catch (\Exception $e) {
                    $connection->rollback();
                    throw $e;
                }
            }
        }
        $form->get('submit')->setValue($this->translate('Update'));
        $form->setData($server->toArray());
        
        return array(
            'form' => $form,
            'server' => $server,
        );
    }
    
    public function deleteAction()
    {
        $serverId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        /* @var $server \HtAuthentication\Model\Server */
        $server = $sl->get('Server');
        
        if (!$server->load($serverId)) {
            $this->gotoIndex($this->translate('No server found.'));
        }
        
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $server->delete();
            $connection->commit();
            $this->gotoIndex($this->translate('Delete server successfully.'), false);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
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
        return $this->redirect()->toRoute('admin/server');
    }
}
