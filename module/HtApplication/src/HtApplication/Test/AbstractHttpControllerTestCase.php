<?php
/**
 * Base Tescase for HtAuthentication module
 * 
 * @see http://www.afewmorelines.com/mocking-user-identities-in-zf2-action-controller-unit-tests/
 */
namespace HtApplication\Test;

use HtUser\Model\User;
use HtAuthentication\Model\Server;
use HtAuthentication\Model\Application;
use AppCore\Mvc\Model\Model;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as ZendAbstractHttpControllerTestCase;
use Zend\Authentication\Storage\Session;

class AbstractHttpControllerTestCase extends ZendAbstractHttpControllerTestCase
{
    public function setUp()
    {
        chdir(ROOT_PATH);
		$this->setApplicationConfig(include ROOT_PATH . '/config/application.config.php');
		parent::setUp();
    }
	/**
	 * @note: copy code from HtApplication\Controller\AbstractActionController.php -> logUserIn()
	 * When log user in using $this->dispatch('/internal/login'), the response object have a redirect 
	 * AS we can not reset response object and keep login session in the same time
	 * SO we have this custom login method which help response object untouched
	 */
	protected function logUserIn($identity, $authenticationProvider = "internal", $oauthUserId = null)
	{
		$identity->authenticationProvider = $authenticationProvider;
		if (isset($oauthUserId)) {
			$identity->oauthProviderId = $oauthUserId;
		}

		/* @var $storage \Zend\Authentication\Storage\StorageInterface */
		$sl = $this->getApplicationServiceLocator();
		/* @var $auth \Zend\Authentication\AuthenticationServie */
		$auth = $sl->get("AuthenticateService");
		/* @var $sessionManager \Zend\Session\SessionManager */
		$sessionManager = $sl->get("Zend\Session\SessionManager");
		//Initializing Session Storage
		$storage = new Session(null, null, $sessionManager);
		$auth->setStorage($storage);
		$storage->write($identity);
	}

	protected function assertResponseHasString($string)
	{
		$contents = $this->getResponse()->getContent();
		$this->assertContains($string, $contents);
	}

	protected function assertResponseIsJson()
	{
        $this->assertJson($this->getResponse()->getContent());
	}

	protected function getResponseObjectFromJson()
	{
		$json = $this->getResponse()->getContent();
        
		return json_decode($json);
	}

	/**
	 * Call this inside test, not in setup.
	 * Call before dispatch
	 */
	protected function mockSessionManager()
	{
		$this->getApplicationServiceLocator()->setAllowOverride(true);
		$sessionManager = $this->getMock('Zend\Session\SessionManager', array('regenerateId'));
		$sessionManager->expects($this->any())
				->method('regenerateId')
				->will($this->returnValue(true));
		$this->getApplicationServiceLocator()->setService('Zend\Session\SessionManager', $sessionManager);
	}
	
	protected function getUniqueEmail($domain = 'gmail.com')
	{
		return 'u' . $this->createRandomNumber() . "@" . $domain;
	}
	
	/**
	 * 
	 * @param int $userId
	 * @param string $logType
	 * @return int
	 */
	protected function countLog($userId, $logType)
	{
		$sm = $this->getApplicationServiceLocator();
		$log = $sm->get('HtApplication\Model\Log');
		return $log->count(array(
					'user_id' => $userId,
					'type' => $logType
		));
	}
	
	const ERROR = 'error';
	const SUCCESS = 'success';
	const INFO = 'info';
	/**
	 * Call before dispatch
	 * @param type $message
	 * @param type $messageType
	 */
	protected function assertFlashMessengerHas($message, $messageType)
	{
		$sl = $this->getApplicationServiceLocator();
		$sl->setAllowOverride(true);
		$methodsMap = array(
			self::ERROR => 'addErrorMessage',
			self::SUCCESS => 'addSuccessMessage',
			self::INFO => 'addInfoMessage',
		);
		$flashMessenger = $this->getMock('Zend\Mvc\Controller\Plugin\FlashMessenger', array($methodsMap[$messageType]));
		$flashMessenger->expects($this->once())
				->method($methodsMap[$messageType])
				->with($message);
		$sl->get('ControllerPluginManager')->setService('flashMessenger', $flashMessenger);		
	}
	const DATABASE_EXCEPTION_MESSAGE = 'database exception.';

	/**
	 * 
	 * @param string $name must instanceof AppCore\Mvc\Model\Model
	 * @param array | string $methods method want throw exception.
	 * @return void
	 * @throws \Exception
	 */
	protected function mockDatabaseException($name, $methods = null)
	{
		$sl = $this->getApplicationServiceLocator();
		$model = $sl->get($name);
		
		$class = get_class($model);
		
		if (!($model instanceof Model)) {
			throw new \Exception('$classModel must instanceof AppCore\Mvc\Model\Model');
		}
		if ($methods === null) {
			$methods = array('save', 'delete');
		} elseif (is_string($methods)) {
			$methods = (array) $methods;
		} else {
			throw new \Exception('$methods must is string or array.');
		}
		
		$mockModel = $this->getMock($class, $methods);
		$mockModel->setTable($model->getTable());
		
		foreach ($methods as $method) {
			$mockModel->expects($this->any())
				->method($method)
				->will($this->throwException(new \Exception(self::DATABASE_EXCEPTION_MESSAGE)));
		}
		
		$factory = function ($sm)  use ($mockModel) {
			$mockModel->setServiceManager($sm);
			return $mockModel;
		};
		$sl->setAllowOverride(true);
		$sl->setFactory($name, $factory);
	}
	
	/**
	 * @return \HtUser\Model\User
	 */
	protected function createUserWithInternalAuth($email = null)
	{
		$sl = $this->getApplicationServiceLocator();

		if ($email === null) {
			$email = $this->getUniqueEmail();
		}
		/* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
		$number = $this->createRandomNumber();
		$data = array(
			'email' => $email,
			'username' => $email,
			'password' => '123456',
			'firstname' => 'Firstname ' . $number,
			'lastname' => 'Lastname' . $number
		);
		$user->exchangeArray($data);
		$requiredActivation = false;
		$user->registerMemberAccount($requiredActivation);

		/* @var $internal \HtAuthentication\Model\Adapter\Internal */
		$internal = $sl->get('AuthAccountInternal');

		$internal->exchangeArray($data);
		$internal->register($requiredActivation);
		$internal->linkUser($user);

		return $user;
	}
	
	/**
	 * Create a IPv4 address.
	 * @return string
	 */
	protected function createIpv4()
	{
		return implode('.', array(rand(1, 255), rand(0, 255), rand(0, 255), rand(1, 254)));
	}
	
	/**
	 * @param \HtAuthentication\Model\Server | int $server
	 * @param \HtAuthentication\Model\Application | int $app
	 */
	protected function linkServerToApp($server, $app)
	{
		
		if ($server instanceof Server) {
			$serverId = $server->getServerId();
		} else {
			$serverId = (int) $server;
		}
		
		if ($app instanceof Application) {
			$applicationId =  $app->getApplicationId();
		} else {
			$applicationId = (int) $app;
			$app = $this->getApplicationServiceLocator()->get('App');
			$app->load($applicationId);
		}
		
		if(!$app->hasServer($server)) {
			$applicationServer = $this->getApplicationServiceLocator()->get('AppServer');
			/* @var $applicationServer \HtAuthentication\Model\ApplicationServer */
			$applicationServer->setApplicationId($applicationId);
			$applicationServer->setServerId($serverId);
			$applicationServer->setCreationDate(date('Y-m-d H:i:s'));
			$applicationServer->save();
		}
	}
	
	/**
	 * 
	 * @param array $data
	 * @return Application
	 */
	protected function createApplication($data = array())
	{
		$randNumber = $this->createRandomNumber();
		$defaults = array(
			'title' => 'Application Test ' . $randNumber,
			'description' => 'Description Application Test '. $randNumber,
			'creation_date' => date('Y-m-d H:i:s'),
		);
		$data = array_merge($defaults, $data);
		
		$sl = $this->getApplicationServiceLocator();
		/* @var $application \HtAuthentication\Model\Application */
		$application = $sl->get('App');
		$application->exchangeArray($data);
		$application->save();
		return $application;
	}
	
		/**
	 * 
	 * @param array $data
	 * @return Server
	 */
	protected function createServer($data = array())
	{
		$number = $this->createRandomNumber();
		$ip = $this->createIpv4();
		$currentTime = date('Y-m-d H:i:s');
		$secret = substr(sha1($currentTime . $ip . $number), 0, 32);
		$defaults = array(
			'title' => 'Server Test ' . $number,
			'description' => 'Description Server Test ' . $number,
			'ip' => $ip,
			'domain' => 'server-' . $number . '.hometradies.com',
			'location' => '',
			'status' => 1,
			'creation_date' => $currentTime,
			'secret' => $secret
		);
		$data = array_merge($defaults, $data);
		/* @var $server \HtAuthentication\Model\Server */
		$server = $this->getApplicationServiceLocator()->get('Server');
		$server->exchangeArray($data);
		$server->save();
		return $server;
	}
	
	protected function createRandomNumber()
	{
		return rand(100000000, 999999999);
	}
	
	/**
	 * @param array $data
	 * @return \HtAuthentication\Model\Organisation
	 */
	protected function createOrganisation($data = array())
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $organisation \HtAuthentication\Model\Organisation */
		$organisation = $sl->get('Org');
		$num = $this->createRandomNumber();
		$default = array(
			'title' => 'Organisation Test ' . $num,
			'description' => 'Organisation Description ' . $num,
			'domain' => 'organistion-test-' . $num . '.com'
		);
		
		$data = array_merge($default, $data);
		$organisation->exchangeArray($data);
		$organisation->setCreationDate(date('Y-m-d H:i:s'));
		$organisation->save();
		return $organisation;
	}

	public function reset($keepPersistence = false)
	{
		$this->closeDbConnnection();
		parent::reset($keepPersistence);
	}

	protected function tearDown()
	{
		$this->closeDbConnnection();
		parent::tearDown();
	}
	
	protected function closeDbConnnection()
	{
		$sl = $this->getApplicationServiceLocator();
		/* @var $dbAdapter \Zend\Db\Adapter\Adapter */
		$dbAdapter = $sl->get('Zend\Db\Adapter\Adapter');
		$dbAdapter->getDriver()->getConnection()->disconnect();
	}
    
    /**
     * Generates an url given the name of a route.
     * Not support full url (included domain) with 'force_canonical' options
     * @see    Zend\Mvc\Router\RouteInterface::assemble()
     * @param string $name
     * @param array $params
     * @param array $options
     * @return string
     */
    protected function fromRoute($name, $params = array(), $options = array())
    {
        $sl = $this->getApplicationServiceLocator();
        $router = $sl->get('Router');
        $options['name'] = $name;
        return $router->assemble($params, $options);
    }
}