<?php
namespace HtApplication\Model;

use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail as ZendMail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Mail
{
	protected $serviceLocator;
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}
	
	public function setServiceLocator($serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}
	
	/**
	 * Will send the mail immediately, not save to database and use mail queue later.
	 * @var boolean
	 */
	protected $sendNow;
	
	public function getSendNow() {
		return $this->sendNow;
	}
	
	public function setSendNow($sendNow) {
		$this->sendNow = $sendNow;
		return $this;
	}
	
	public function send($subject, $body, $recipientAddress, $recipientName, $senderAddress = null, $senderName = null)
	{
		$sl = $this->getServiceLocator();
		$config = $sl->get('config');
		if (!isset($senderAddress)) {
			$senderAddress = $config['mail']['senderAddress'];
			$senderName = $config['mail']['senderName'];
		}
		
		//force send now
		$sendNow = false;
		if ($this->getSendNow()) {
			$sendNow = true;
		}
		
		//if no setting for send mail using cron -> send now
		//if there is setting for send mail using cron -> send using cron
		if (!$sendNow) {
			if (array_key_exists('sendMailUsingCron', $config['mail'])) {
				if ($config['mail']['sendMailUsingCron']) {
					$sendNow = false;
				} else {
					$sendNow = true;
				}
			} else {
				$sendNow = true;
			}
		}
		
		if ($sendNow) {
			switch ($config['emailTransport']['transport']) {
				case 'smtp':
					$transport = $this->getServiceLocator()->get('HtApplication\Mail\Transport\Smtp');
					break;
				case 'file':
					$transport = $this->getServiceLocator()->get('HtApplication\Mail\Transport\File');
					break;
				default:
					throw new \Exception("Not support mail transport");
			}
			
			$htmlBody = new MimePart($body);
			
			$body = new MimeMessage();
			$body->setParts(array($htmlBody));
			
			$mail = new ZendMail\Message();
			$mail->setEncoding("UTF-8");
			$mail->setFrom($senderAddress, $senderName);
			$mail->setSubject($subject);
			$mail->setBody($body);
			$mail->addTo($recipientAddress, $recipientName);
			// to show proper html on yahoo
			$mail->getHeaders()->get('content-type')->setType('text/html');
			$transport->send($mail);			
		} else {
			$mailData = array(
				'subject' => $subject,
				'body' => $body,
				'sender' => $senderAddress,
				'sender_name' => $senderName,
				'recipient' => $recipientAddress,
				'recipient_name' => $recipientName,
			);
			//save to cron job and send later
			/* @var $mailQueue \HtApplication\Model\MailQueue */
			$mailQueue = $this->getServiceLocator()->get('HtApplication\Model\MailQueue');
			$mailQueue->save($mailData);
		}
	}
}