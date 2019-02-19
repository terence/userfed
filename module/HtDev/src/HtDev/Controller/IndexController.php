<?php
/**
 * Create mock data.
 * 
 * @link http://framework.zend.com/manual/2.3/en/modules/zend.console.controllers.html
 * @link http://framework.zend.com/manual/2.3/en/modules/zend.console.routes.html
 * @link https://github.com/fzaninotto/Faker
 */
namespace HtDev\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Faker\Factory as FakerFactory;
use HtDev\Faker\Application as ApplicationFakerProvider;

use Zend\Dom\Query;

class IndexController extends AbstractActionController
{
    /**
     * Generate xss attack content or not
     * @var boolean
     */
    protected $xss = false;
    
    public function mockUserAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        
        $time1 = time();
        
        $userCount = $request->getParam('usercount', 30);
        $this->xss = $request->getParam('xss', false);
        $faker = FakerFactory::create();
        $sl = $this->getServiceLocator();
        for ($i = 0; $i < $userCount; $i++) {
            $this->createUser($faker, $sl);
        }
        
        $message = "User: $userCount \n";
        $message .= "Run time: " . $this->duration($time1) . "\n";
        return $message;
    }
            
    public function mockDataAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }
        
        $this->xss = $request->getParam('xss', false);
        
        $time1 = time();
        
        $faker = FakerFactory::create();
        $sl = $this->getServiceLocator();
        
        $faker->addProvider(new ApplicationFakerProvider($faker));
        
        $orgCount = $request->getParam('orgcount', 30);
        $appCount = $request->getParam('appcount', 20);
        for ($i = 0; $i < $orgCount; $i++) {
            if ($i % 2 == 0) {
                $this->createOrganisationWithoutUser($faker, $sl);
            } else {
                $userCount = $i;
                if ($i > 12) {
                    $userCount = 12;
                }
                $this->createOrganisationWithUser($faker, $sl, $userCount);
            }
        }
        
        for ($i = 0; $i < $appCount; $i++) {
            $this->createApp($faker, $sl);
        }
        
        $message = "Org: $orgCount \n";
        $message .= "App: $appCount \n";
        $message .= "Run time: " . $this->duration($time1) . "\n";
        return $message;
    }
    
    /**
     * Build duration string
     * 
     * @param int $time1
     * @param int|null $time2
     * @return string
     */
    protected function duration($time1, $time2 = null)
    {
        if (!isset($time2)) {
            $time2 = time();
        }
        
        $duration = $time2 - $time1;
        if ($duration/60 < 1) {
            $duration = $duration . " (s)";
        } else {
            $duration = floor($duration/60) . " (m) ";
            $seconds = $duration % 60;
            if ($seconds > 0) {
                $duration .= $seconds . " (s)";
            }
        }
        
        return $duration;
    }
    
    protected function createApp($faker, $sl)
    {
        /* @var $app \HtAuthentication\Model\Application */
        $app = $sl->get('App');
        $app->exchangeArray(array(
            'title' => $faker->appName,
            'description' => $faker->sentence
        ));
        $app->save();
        $app->setApplicationId(null);
    }
    
    /**
     * Add XSS hack to data
     */
    protected function xss($string)
    {
        //@link http://framework.zend.com/manual/2.1/en/modules/zend.escaper.escaping-html-attributes.html
        //HTML Attribute
        $input1 = <<<INPUT
' onmouseover='alert(/Hacked in html attribute!/);
INPUT;
        //HTML Attribute
        $input2 = <<<INPUT
" onmouseover="alert(/Hacked in html attribute!/);
INPUT;
        //HTML Attribute
        $input3 = <<<INPUT
faketitle onmouseover=alert(/Hacked in html attribute!/);
INPUT;
        //Javascript
        //@link http://framework.zend.com/manual/2.1/en/modules/zend.escaper.escaping-javascript.html
        $input4 = <<<INPUT
bar&quot;; alert(&quot;Hacked in javascript attribute&quot;); var xss=&quot;true
INPUT;
        
        //Css 
        //@link http://framework.zend.com/manual/2.1/en/modules/zend.escaper.escaping-css.html
        $input5 = <<<INPUT
body {
    background-image: url('http://example.com/foo.jpg') 
} 
</style>
<script>alert('Hacked in style')</script>
INPUT;
        
        //Url
        //@link http://framework.zend.com/manual/2.1/en/modules/zend.escaper.escaping-url.html
        $input6 = <<<INPUT
" onmouseover="alert('Hacked in url')
INPUT;
        $input7 = <<<INPUT
' onmouseover='alert("Hacked in url")
INPUT;
        
        //hrml
        //@link http://framework.zend.com/manual/2.1/en/modules/zend.escaper.escaping-html.html
        $input8 = "<script>alert('Hacked in html');</script>";
        
        $inputs = array($input1, $input2, $input3, $input4, $input5, $input6, $input7, $input8);
        
        if ($this->xss) {
            return $string . $inputs[rand(0,7)];
        } else {
            return $string;
        }
        
    }
    
    /**
     * 
     * @param type $faker
     * @param type $sl
     * @param type \HtAuthentication\Model\Organisation
     */
    protected function createUser($faker, $sl, $org = null)
    {
        //generate user
        /* @var $user \HtUser\Model\User */
		$user = $sl->get('User');
        
        /* @var $authInternal \HtAuthentication\Model\Adapter\Internal */
		$authInternal = $sl->get('AuthAccount\Internal');

        $firstname = (rand(0,2) % 2) == 0 ? $faker->firstNameFemale : $faker->firstNameMale;
        $email = $faker->email;
        $user->exchangeArray(array(
            'firstname' => $this->xss($firstname),
            'lastname' => $this->xss($faker->lastname),
            'email' => $this->xss($email)

        ));
        $user->registerMemberAccount(false);
        $authInternal->exchangeArray(array(
            'username' => $this->xss($email),
            'password' => $this->xss('123456')
        ));
        $authInternal->register(false);

        $authInternal->linkUser($user);

        /* @var $org \HtAuthentication\Model\Organisation */
        if ($org) {
            $org->addUser($user);
        }
        
        //reset so next time we will create instead of update
        $authInternal->setAuthenticationInternalId(null);
        $user->setUserId(null);
        /* @var $authAccount \HtAuthentication\Model\AuthenticationAccount */
        $authAccount = $sl->get('AuthAccount');
        $authAccount->setAuthenticationAccountId(null);
    }
    
    protected function createOrganisationWithoutUser($faker, $sl)
    {
        /* @var $org \HtAuthentication\Model\Organisation */
		$org = $sl->get('Org');
        $org->exchangeArray(array(
            'title' => $faker->company,
            'description' => $faker->catchPhrase,
            'domain'    => $faker->domainName
        ));
        $org->save();
        
        //reset for later create
        $org->setOrganisationId(null);
    }
    
    protected function createOrganisationWithUser($faker, $sl, $userCount = 20)
    {
        /* @var $org \HtAuthentication\Model\Organisation */
		$org = $sl->get('Org');
        $org->exchangeArray(array(
            'title' => $faker->company,
            'description' => $faker->catchPhrase,
            'domain'    => $faker->domainName
        ));
        $org->save();
        
        for($i = 0; $i < $userCount; $i++) {
            $this->createUser($faker, $sl, $org);
        }
        
        //reset for later create
        $org->setOrganisationId(null);        
    }
    
    public function crawlSoftwareNameAction()
    {
        //source from http://www.softpedia.com/topdownloads/
        $html = file_get_contents("c:/tmp/debug.txt");
        $dom = new Query($html);
        $results = $dom->execute('h2 a');

        $found = '$appNames = array(' . "\n";
        foreach ($results as $result) {
            // $result is a DOMElement
            if (!empty($result->textContent)) {
                $appName = $result->textContent;
                //clean "123. " in app name
                $appName = substr($appName, strpos($appName, ".") + 2);
                $appName = str_replace('....', '', $appName);
                $appName = str_replace('...', '', $appName);
                $found .= "    '" . $appName . "',\n";
            }
        }
        $found .= ");\n";
        
        file_put_contents("c:/tmp/debug1.txt", $found);
    }
}