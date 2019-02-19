<?php
namespace AppCoreTest\Mvc\Model\SampleModel;

use AppCore\Mvc\Model\Model as BaseModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class Model2 extends BaseModel implements SoftDeleteableInterface
{
	protected $mapping = array(
		array('dbColumn' => 'model2_id', 'objectProperty' => 'model2Id', 'isIdentifier' => true),
		array('dbColumn' => 'name', 'objectProperty' => 'name'),
	);
	
	protected $model2Id;
	protected $name;
	public function getModel2Id()
	{
		return $this->model2Id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setModel2Id($model2Id)
	{
		$this->model2Id = $model2Id;
	}

	public function setName($name)
	{
		$this->name = $name;
	}


}