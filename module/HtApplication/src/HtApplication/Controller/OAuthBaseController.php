<?php

namespace HtApplication\Controller;
use HtAuthentication\Model\Adapter\OAuth;
class OAuthBaseController extends AbstractActionController
{
	/**
	 * 
	 * @param string $oauth
	 */
	protected function patchHttpClient($oauth)
	{
		$config = $this->getConfig();
		
		//turn off ssl verification in local-none-internet environment
		if (array_key_exists('local', $config['reverseoauth2'])) {
			if ($config['reverseoauth2']['local'] === true) {
				/* @var $client \Zend\Http\Client */
				$client = $oauth->getHttpClient();
				/* @var $curlClient \Zend\Http\Client\Adapter\Curl */
				$curlClient = $client->getAdapter();
				$curlClient->setCurlOption(CURLOPT_SSL_VERIFYHOST, 0);
				$curlClient->setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);
			}
		}
	}
	
	/**
	 * After request ReverseOAuth2 cache info OAuthentication
	 * So if user want login other Account
	 * in provider (E.g: Other account facebook) must clear cache.
	 */	
	protected function resetOAuthClient($oauth)
	{		
		$sl = $this->getServiceLocator();
		/* @var $sessionManager \Zend\Session\SessionManager */
		$sessionManager = $sl->get('Zend\Session\SessionManager');
		$oauthSessionName = $oauth->getSessionContainer()->getName();
		$sessionManager->getStorage()->clear($oauthSessionName);
		
	}
	
	/**
	 * @param string $provider
	 * @return AbstractOAuth2Client
	 */
	protected function getOAuthClient($provider, $action)
	{
		$sl = $this->getServiceLocator();
		/* @var $oauth \ReverseOAuth2\OAuth2HttpClient */
		$oauth = $sl->get(OAuth::getProviderService($provider));
		
		$callbackUrl = $this->url()->fromRoute('oauth', array('action' => $action, 'provider' => $provider), array('force_canonical' => true));
		$oauth->getOptions()->setRedirectUri($callbackUrl);
		$this->patchHttpClient($oauth);
		
		return $oauth;
	}
}
