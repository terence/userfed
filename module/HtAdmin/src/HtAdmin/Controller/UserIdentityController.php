<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractActionController;

use HtAuthentication\Model\AuthenticationAccount as AuthAccount;
use HtApplication\Model\Log;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Null as PaginatorNull;

class UserIdentityController extends AbstractActionController
{
    public function indexAction()
    {
        $userId = $this->params()->fromRoute('id');
        $sl = $this->getServiceLocator();
        $user = $sl->get('User');
        
        if (!$user->load($userId)) {
            $this->gotoUserPage($this->translate('User not found.'));
        }
        /* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
        $authAccountCollection = $sl->get('AuthAccountCollection');
        $count = $authAccountCollection->count(array('user_id' => $userId));
        $pagingParamsData = $this->pagingParams()->get($count);
        $logins = $authAccountCollection->getAllLogin($userId, $pagingParamsData['limit'], $pagingParamsData['offset']);
        
        $paginator = new Paginator(new PaginatorNull($count));
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
            'user' => $user,
            'logins' => $logins,
            'totalLogins' => $count,
            'paginator' => $paginator,
            'errorMessage' => $errorMessage,
			'successMessage' => $successMessage,
        );
    }
    
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        
        /* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
        $sl = $this->getServiceLocator();
        $authAccount = $sl->get('AuthAccount');
        if (!$authAccount->load($id)) {
            return $this->gotoUserPage($this->translate('Identity not found.'));
        }
        
        $userId = $authAccount->getUserId();
        if ($authAccount->count(array('user_id' => $userId)) == 1) {
            return $this->gotoIdentityPage(
                    $userId,
                    $this->translate('You can\'t delete this identity because this is user\'s last remaining one.')
            );
        }

        $identity = $sl
                ->get('AuthAccount\\' . $authAccount->getType())
                ->getOne(array('authentication_account_id' => $authAccount->getAuthenticationAccountId()));

        if ($identity) {
            try {
                $connection = $this->getConnection();
                $connection->beginTransaction();
                $identity->delete();
                $authAccount->delete();

                $message = $this->translate('Admin delete internal identity');

                if ($authAccount->getType() == AuthAccount::TYPE_OAUTH) {
                    $message = sprintf($this->translate('Admin delete %s identity, provider user id %s'),
                            ucfirst($identity->getProvider()),
                            $identity->getProviderUserId()
                            );
                }

                $this->writeLog(
                        Log::TYPE_DELETE_LOGIN_SUCCESS,
                        $message,
                        array('user_id' => $userId
                ));
                $connection->commit();
                return $this->gotoIdentityPage($userId, $this->translate('Delete identity successfully.'), false);
            } catch (\Exception $e) {
                $connection->rollback();
                throw $e;
            }
        }
    }
    
    protected function gotoIdentityPage($userId, $message = null, $error = true )
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        
        return $this->redirect()->toRoute('admin/user/identity', array('id' => $userId));
    }


    protected function gotoUserPage($message = null, $error = true)
    {
        if ($message) {
            if ($error) {
                $this->flashMessenger()->addErrorMessage($message);
            } else {
                $this->flashMessenger()->addSuccessMessage($message);
            }
        }
        return $this->redirect()->toRoute('admin/user');
    }
}
