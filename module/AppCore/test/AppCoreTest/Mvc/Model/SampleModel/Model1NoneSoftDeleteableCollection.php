<?php
namespace AppCoreTest\Mvc\Model\SampleModel;

use AppCore\Mvc\Model\Collection;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class Model1NoneSoftDeleteableCollection extends Collection// implements SoftDeleteableInterface
{
	/**
	 * Coun all element including soft-deleted ones
	 * @return type
	 */
	public function getCustomCount()
	{
		return $this->getTableGateway("", false)->select()->count();
	}
}