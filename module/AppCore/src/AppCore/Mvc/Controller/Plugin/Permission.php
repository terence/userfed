<?php

/**
 * Help to weave the ACL with various logic of application
 * @author Tuan Ngo
 *
 */

namespace AppCore\Mvc\Controller\Plugin;

use Zend\View\Model\JsonModel;
use Zend\Filter\Word;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\Redirect\Response;

class Permission extends AbstractPlugin
{

    /**
     * Check if user can access current module - controller - action
     * @param MvcEvent $e
     * @return boolean
     */
    public function isAllow(MvcEvent $e)
    {
        /* @var $controller \AppCore\Mvc\Controller\AbstractActionController */
        $controller = $this->getController();
        //extract resource name from route
        $params = $this->getModuleControllerActionFromRoute($e);

        //convert module/controller/action to resource/priviledge
        $resource = $params['module'] . ":" . $params['controller'] . ":" . $params['action'];
        $sl = $controller->getServiceLocator();
        /* @var $acl \HtApplication\Model\Acl\Acl */
        $acl = $sl->get('HtApplication\Model\Acl\Acl');
        $acl->init();

        if (!$acl->getAcl()->hasResource($resource)) {
            $resource = $params['module'] . ":" . $params['controller'];
        }

//		$priviledge = $params['action'];
        $priviledge = null;

        /* @var $userRole \HtApplication\Model\Acl\UserRole */
        $userRole = $sl->get('HtApplication\Model\Acl\UserRole');
        $userId = $controller->getLoggedInUserId();
        $userRoles = $userRole->getRoles($userId);
        if (empty($userRoles)) {
            $userRoles = array('guest');
        }
        $allow = false;
        $controller->flashMessenger()->clearMessages();

        foreach ($userRoles as $role) {
            if ($acl->isAllow($role, $resource, $priviledge)) {
                $allow = true;
                break;
            }
        }

        if (!$allow) {
            // get access denied messages
            $message = $controller->translate($controller->getAccessDeniedMessage($resource, $priviledge));
            if (!in_array($message, $controller->flashMessenger()->getErrorMessages())) {
                $controller->flashMessenger()->addErrorMessage($message);
            }
        }
        return $allow;
    }

    protected function getModuleControllerActionFromRoute(\Zend\Mvc\MvcEvent $e)
    {
        /* @var $route \Zend\Mvc\Router\RouteMatch */
        $route = $e->getRouteMatch();
        $controller = $route->getParam('controller');
        $controller = explode("\\", $controller);
        /**
         * Type 1: route define with namespace
         *
         * '__NAMESPACE__' => 'GbAdmin\Controller',
         * 'controller' => 'Index',
         * 'action' => 'index',
         */
        if (count($controller) == 1) {
            //as route get controller from url (not yet parse)
            //so we may get control in form: controller-name,
            //we need to change to ControllerName
            $filter = new Word\DashToCamelCase();
            $controllerName = $filter->filter($controller[0]);

            $moduleName = explode('\\', $route->getParam('__NAMESPACE__'));
            $moduleName = $moduleName[0];
        }
        /**
         * Type 2: route define without namespace
         *
         * 'controller' => 'GbAdmin\Controller\Setting',
         * 'action' => 'index',
         */ else if (count($controller) == 3) {
            $controllerName = $controller[2];
            $moduleName = $controller[0];
        }

        $action = $route->getParam('action');

        return array(
            'module' => $moduleName,
            'controller' => $controllerName,
            'action' => $action
        );
    }

    /**
     * Redirect application flow and do other when authorize process fails
     * @param MvcEvent $e
     * @return Response | null
     */
    public function handleFailedAuthorize(MvcEvent $e)
    {
        /* @var $controller \AppCore\Mvc\Controller\AbstractActionController */
        $controller = $this->getController();

        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $request = $e->getRequest();

        if ($controller->hasIdentity()) {
            if ($request->isXmlHttpRequest()) {
                $viewModel = new JsonModel(array("redirectUrl" => "/access-denied"));
                $e->setViewModel($viewModel);
                return $e->stopPropagation(); /* set Event stop dispatch and return response */
            } else {
                return $controller->redirect()->toRoute("access-denied");
            }
        } else {
            $requestUri = $request->getServer('REQUEST_URI', null);
            if ($request->isXmlHttpRequest()) {
                $ajaxTimeOutErrorCode = 999999;
                $viewModel = new JsonModel(array(
                    "errorCode" => $ajaxTimeOutErrorCode,
                    "redirectUrl" => $controller->url()->fromRoute(
                            'login', array(), array('query' => array('redirect-url' => urlencode($requestUri)))
                    )
                ));
                $e->setViewModel($viewModel);
                return $e->stopPropagation(); /* set Event stop dispatch and return response */
            } else {
                return $controller->redirect()->toRoute('login', array(), array('query' => array('redirect-url' => urlencode($requestUri))));
            }
        }
    }

}
