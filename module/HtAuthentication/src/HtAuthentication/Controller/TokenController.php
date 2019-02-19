<?php
namespace HtAuthentication\Controller;

use HtAuthentication\Controller\TokenBaseController;
use Zend\View\Model\JsonModel;
use HtApplication\Model\Log;

class TokenController extends TokenBaseController
{
	public function verifyAction()
	{
		$result = $this->verifySecretAndToken();
		if ($result instanceof JsonModel) {
			return $result;
		}

		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();
			$data = array(
				'applicationId' => $this->getApplication()->getApplicationId(),
				'userId' => $this->getUser()->getUserId()
			);
			$org = $this->getOrganisation();
			if (isset($org)) {
				$data['orgId'] = $org->getOrganisationId();
			}
			$this->writeLog(Log::TYPE_VERIFY_TOKEN_SUCCESS,
					$this->translate('Verify token success'),
					$data
			);

			$connection->commit();
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}

		//Need to check how to return expired
		return new JsonModel(array_merge($data, array(
					'errorCode' => 0,
					'status' => 'authenticated',
					'dateGenerated' => date('Y-m-d H:i:s')
		)));
	}

	/**
	 * Redirect user to client application
	 */
	public function redirectToClientAppAction()
	{
		if (!$this->hasIdentity()) {
			throw new \Exception("Required login");
		}
		$sl = $this->getServiceLocator();
		$request = $this->getRequest();

		$connection = $this->getConnection();
		try {
			$connection->beginTransaction();

			$serverId = $request->getQuery("server");
			/* @var $server \HtAuthentication\Model\Server */
			$server = $sl->get('Server');
			if (empty($serverId) || !$server->load($serverId)) {
				throw new \Exception("Server not found");
			}

			$appId = $request->getQuery("app");
			/* @var $app \HtAuthentication\Model\Application */
			$app = $sl->get('App');
			if (empty($appId) || !$app->load($appId)) {
				throw new \Exception("Application not found");
			}

			$foundServer = false;
			/* @var $appServer \HtAuthentication\Model\ApplicationServerCollection */
			$appServer = $sl->get('AppServerCollection');
			foreach ($appServer->getServerByApplicationId($app->getApplicationId()) as $s) {
				if ($s->getServerId() == $server->getServerId()) {
					$foundServer = true;
					break;
				}
			}
			if (!$foundServer) {
				throw new \Exception("Request server not belong to request application");
			}

			/* @var $org \HtAuthentication\Model\Organisation */
			$org = $sl->get('Org');
			$orgId = $request->getQuery('org');
			if (!empty($orgId)) {				
				if (!$org->load($orgId)) {
					throw new \Exception("Organisation not found");
				}
			}

			$user = $sl->get("LoggedInUser");

			/* @var $userApp \HtAuthentication\Model\UserApplication */
			$userApp = $sl->get('UserApp');
			$userApp->setServerId($server->getServerId());
			$userApp->setApplicationId($app->getApplicationId());
			$token = null;
			if (empty($orgId)) {
				$token = $userApp->getToken($user);
			} else {
				$token = $userApp->getToken($user, $org);
			}
			$tokenValue = null;
			if ($token) {
				$tokenValue = $token->getToken();
			}
			if (empty($tokenValue)) {
				//user is logged in and has permission to access application/server
				//we need to generate ondemand token here
				if (empty($orgId)) {
					$token = $userApp->generateToken($user);
				} else {
					$token = $userApp->generateToken($user, $org);
				}
				$tokenValue = $token->getToken();

				/* @var $sessionManager \Zend\Session\SessionManager */
				$sessionManager = $sl->get("Zend\Session\SessionManager");
				$token->linkWebSession($sessionManager->getId());
			}

			$serverDomain = $server->getDomain();
			if (empty($serverDomain)) {
				throw new \Exception("No domain for request server, can not redirect");
			}
			//redirect to client application with token
			$url =  $serverDomain . "/userfed/" . $tokenValue;

			$connection->commit();
		} catch(\Exception $e) {
			$connection->rollback();
			throw $e;
		}

		return $this->redirect()->toUrl($url);
	}
}
