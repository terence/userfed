<?php
namespace AppCoreTest\Mvc\Model\SampleModel;

use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class Model1SoftDeleteableCollection extends Collection implements SoftDeleteableInterface
{
	/**
	 * Not count soft-deleted element
	 * 
	 * @return type
	 */
	public function getCustomCount()
	{
		return $this->getTableGateway()->select()->count();
	}
}