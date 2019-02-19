<?php
namespace AppCoreTest\Mvc\Model\SampleModel;

use AppCore\Mvc\Model\Model as BaseModel;
use AppCore\Mvc\Model\SoftDeleteableInterface;
use Zend\Db\Sql\Where;

class Model12 extends BaseModel implements SoftDeleteableInterface
{
	protected $mapping = array(
		array('dbColumn' => 'model12_id', 'objectProperty' => 'model12Id', 'isIdentifier' => true),
		array('dbColumn' => 'model1_id', 'objectProperty' => 'model1Id'),
		array('dbColumn' => 'model2_id', 'objectProperty' => 'model2Id'),
	);
	
	protected $model12Id;
	protected $model1Id;
	protected $model2Id;
	
	public function getModel12Id()
	{
		return $this->model12Id;
	}

	public function getModel1Id()
	{
		return $this->model1Id;
	}

	public function getModel2Id()
	{
		return $this->model2Id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setModel12Id($model12Id)
	{
		$this->model12Id = $model12Id;
	}

	public function setModel1Id($model1Id)
	{
		$this->model1Id = $model1Id;
	}

	public function setModel2Id($model2Id)
	{
		$this->model2Id = $model2Id;
	}

	public function setName($name)
	{
		$this->name = $name;
	}
	
	protected function loadSoftDeletedInstance($where = null)
	{
		$where = new Where();
		$where->equalTo('model1_id', $this->getModel1Id());
		$where->equalTo('model2_id', $this->getModel2Id());
		return parent::loadSoftDeletedInstance($where);
	}
}