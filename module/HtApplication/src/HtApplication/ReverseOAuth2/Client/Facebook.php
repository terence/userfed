<?php

namespace HtApplication\ReverseOAuth2\Client;

use ReverseOAuth2\Client\Facebook as BaseFacebook;
use Zend\Http\Request;

class Facebook extends BaseFacebook
{
	public function getInfo()
    {
        if(is_object($this->session->info)) {
            
            return $this->session->info;
        
        } elseif(isset($this->session->token->access_token)) {
            
            $urlProfile = $this->options->getInfoUri() . '?access_token='.$this->session->token->access_token;
            
            $client = $this->getHttpclient()
                            ->resetParameters(true)
                            ->setHeaders(array('Accept-encoding' => '*'))
                            ->setMethod(Request::METHOD_GET)
                            ->setUri($urlProfile);
            
            $retVal = $client->send()->getContent();

            if(strlen(trim($retVal)) > 0) {
                
                $this->session->info = \Zend\Json\Decoder::decode($retVal);
                return $this->session->info;
                
            } else {
                
                $this->error = array('internal-error' => 'Get info return value is empty.');
                return false;
                
            }
            
        } else {
            
            $this->error = array('internal-error' => 'Session access token not found.');
            return false;
            
        }
    }
}
