<?php
namespace AppCoreTest\Mvc\Model\SampleModel;

use AppCore\Mvc\Model\Model as BaseModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;

class Model1 extends BaseModel implements SoftDeleteableInterface
{
	protected $mapping = array(
		array('dbColumn' => 'model1_id', 'objectProperty' => 'model1Id', 'isIdentifier' => true),
		array('dbColumn' => 'name', 'objectProperty' => 'name'),
	);
	
	protected $model1Id;
	protected $name;
	public function getModel1Id()
	{
		return $this->model1Id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setModel1Id($model1Id)
	{
		$this->model1Id = $model1Id;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}


}