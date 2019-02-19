<?php
/**
 * If config with send mail in batch using cron job
 * - Store email in database AND
 * - Send email through cron job 
 *
 * If no config of cron job.
 * - Just send straight away
 */
namespace HtApplication\Model;

use AppCore\Mvc\Model\Model as AppCoreModel;

class MailQueue extends AppCoreModel
{
	protected $mailQueueId;
	protected $subject;
	protected $body;
	protected $sender;
	protected $senderName;
	protected $recipient;
	protected $recipientName;
	
	protected $mapping = array(
			array('dbColumn' => 'mail_queue_id', 'objectProperty' => 'mailQueueId', 'isIdentifier' => true),
			array('dbColumn' => 'subject', 'objectProperty' => 'subject'),
			array('dbColumn' => 'body', 'objectProperty' => 'body'),
			array('dbColumn' => 'sender', 'objectProperty' => 'sender'),
			array('dbColumn' => 'sender_name', 'objectProperty' => 'senderName'),
			array('dbColumn' => 'recipient', 'objectProperty' => 'recipient'),
			array('dbColumn' => 'recipient_name', 'objectProperty' => 'recipientName'),			
	);
		
	public function getMailQueueId() {
		return $this->mailQueueId;
	}
	
	public function setMailQueueId($mailQueueId) {
		$this->mailQueueId = $mailQueueId;
		return $this;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	
	public function getSender() {
		return $this->sender;
	}
	
	public function setSender($sender) {
		$this->sender = $sender;
		return $this;
	}
	
	public function getSenderName() {
		return $this->senderName;
	}
	
	public function setSenderName($senderName) {
		$this->senderName = $senderName;
		return $this;
	}
	
	public function getRecipient() {
		return $this->recipient;
	}
	
	public function setRecipient($recipient) {
		$this->recipient = $recipient;
		return $this;
	}
	
	public function getRecipientName() {
		return $this->recipientName;
	}
	
	public function setRecipientName($recipientName) {
		$this->recipientName = $recipientName;
		return $this;
	}
		
	/**
	 * send emails in mail_queue
	 * @TODO: move to another place later
	 */
	public function proccessPendingMail()
	{
		// paging
		// number of invitation mails will be sent per one cron job
		$limit = 500;
		$rowset = $this->select(function(Select $select) use ($limit) {
			$select->offset(0)->limit($limit);
		});
		if (!$rowset->count()) {
			return;
		}
		$mails = $rowset->toArray();
		$mailQueueIds = array();
		foreach ($mails as $mail) {
			$subject = $mail['subject'];
			$body = $mail['body'];
			$senderAddress = $mail['sender'];
			$senderName = $mail['sender_name'];
			$recipientAddress = $mail['recipient'];
			$recipientName = $mail['recipient_name'];
			$this->sendMail($subject, $body, $recipientAddress, $recipientName, $senderAddress, $senderName);
			$mailQueueIds[] = $mail['mail_queue_id'];
		}
		$this->delete(array('mail_queue_id' => $mailQueueIds));
	}
	
	protected function sendMail($subject, $body, $recipientAddress, $recipientName, $senderAddress, $senderName)
	{
		/*@var $mail \HtApplication\Model\Mail */
		$mail = $this->getServiceManager()->get('HtApplication\Model\Mail');
		$mail->setSendNow(true);
		
		$mail->send($subject, $body, $recipientAddress, $recipientName, $senderAddress, $senderName);
	}
}