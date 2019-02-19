<?php

namespace AppCoreTest\Mvc\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
//use Zend\Db\Sql\Ddl;
//use Zend\Db\Sql\Ddl\Column;
//use Zend\Db\Sql\Ddl\Constraint;

class SoftDeteleableModelTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var AppCoreTest\Mvc\Model\Model
	 */
	public $model;
	public $dbAdapter;
	public $sl;
	/**
	 * @var TableGateway
	 */
	public $model1TableGateway;
	
	public function setUp()
	{
		$sl = $this->sl = \AppCoreTest\Bootstrap::getServiceManager();
		$db = $this->dbAdapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		//create table
		$ddlQuery = file_get_contents(realpath(__DIR__ . '/SampleModel/schema.sql'));
		$db->query($ddlQuery, $db::QUERY_MODE_EXECUTE);
		
		$this->model1TableGateway = new TableGateway('model1table', $this->dbAdapter);
		
	}
	
	
	public function tearDown()
	{
		
	}
	
	/**
	 * @return \AppCoreTest\Mvc\Model\SampleModel\Model1
	 */
	protected function getModel()
	{
		$table = new SampleModel\Model1Table($this->dbAdapter);
		$model = new SampleModel\Model1();
		$model->setServiceManager($this->sl);
		$model->setTable($table);
		return $model;
	}
	
	/**
	 * @return \AppCoreTest\Mvc\Model\SampleModel\Model1
	 */
	protected function createModelInstance()
	{
		$model = $this->getModel();
		$model->exchangeArray(array(
			'name' => 'test'
		));
		$model->save();
		return $model;
	}
	
	/**
	 * @param int $id
	 * @return array
	 */
	protected function getOneById($id)
	{
		return $this->model1TableGateway->select(array('model1_id' => $id))->current()->getArrayCopy();
	}
	
	public function testCreate()
	{
		$model = $this->getModel();
		$model->exchangeArray(array(
			'name' => 'test'
		));
		$model->save();
		
		$row = $this->getOneById($model->getModel1Id());
		$this->assertEquals($row['model1_id'], $model->getModel1Id());
		$this->assertEquals($row['is_deleted'], 0);
	}
	
	public function testDelete()
	{
		$model = $this->createModelInstance();
		
		$row = $this->getOneById($model->getModel1Id());
		$this->assertEquals($row['model1_id'], $model->getModel1Id());
		$this->assertEquals($row['is_deleted'], 0);
		
		$model->delete();
		$row = $this->getOneById($model->getModel1Id());
		$this->assertEquals($row['model1_id'], $model->getModel1Id());
		$this->assertEquals($row['is_deleted'], 1);		
	}
	
	public function testGetOne()
	{
		$model = $this->createModelInstance();
		
		$model1 = $this->getModel();
		$this->assertNotNull($model1->getOne(array('model1_id' => $model->getModel1Id())));
		
		$model->delete();
		
		$this->assertNull($model1->getOne(array('model1_id' => $model->getModel1Id())));
	}
	
	public function testGetAll()
	{
		$model = $this->createModelInstance();
		
		$model1 = $this->getModel();
		$rows1 = $model1->getAll();
		
		$model->delete();
		
		$rows2 = $model1->getAll();
		
		$this->assertEquals(count($rows2), count($rows1) - 1);
	}
	
	public function testCount()
	{
		$model = $this->createModelInstance();
		
		$model1 = $this->getModel();
		$count1 = $model1->count();
		
		$model->delete();
		
		$count2 = $model1->count();
		
		$this->assertEquals($count2, $count1 - 1);
	}
	
	public function testGetTableGateway()
	{
		$table = new SampleModel\Model1Table($this->dbAdapter);
		$model1SoftDeleteableCollection = new SampleModel\Model1SoftDeleteableCollection();
		$model1SoftDeleteableCollection->setServiceManager($this->sl);
		$model1SoftDeleteableCollection->setTable($table);
		
		$table = new SampleModel\Model1Table($this->dbAdapter);
		$model1NoneSoftDeleteableCollection = new SampleModel\Model1NoneSoftDeleteableCollection();
		$model1NoneSoftDeleteableCollection->setServiceManager($this->sl);
		$model1NoneSoftDeleteableCollection->setTable($table);

		$count1 = $model1SoftDeleteableCollection->getCustomCount();
		$count2 = $model1NoneSoftDeleteableCollection->getCustomCount();

		$model = $this->createModelInstance();
		$model->delete();
		
		$count11 = $model1SoftDeleteableCollection->getCustomCount();
		$count21 = $model1NoneSoftDeleteableCollection->getCustomCount();
		
		$this->assertEquals($count1, $count11);
		$this->assertEquals($count2 + 1, $count21);
	}
	
	public function testHarDelete()
	{
		//option 1
		$count1 = $this->model1TableGateway->select()->count();
		
		$model = $this->createModelInstance();
		$count2 = $this->model1TableGateway->select()->count();
		
		$model->disableSoftDelete();
		$model->delete();
		$count3 = $this->model1TableGateway->select()->count();
	
		$this->assertEquals($count2, $count1 + 1);
		$this->assertEquals($count3, $count1);
		
		//option 2
		$count1 = $this->model1TableGateway->select()->count();
		
		$model = $this->createModelInstance();
		$count2 = $this->model1TableGateway->select()->count();
		
		$model->hardDelete();
		$count3 = $this->model1TableGateway->select()->count();
	
		$this->assertEquals($count2, $count1 + 1);
		$this->assertEquals($count3, $count1);

	}
	
	public function testRestore()
	{
		$model = $this->createModelInstance();
		$id = $model->getModel1Id();
		$row = $this->getOneById($id);
		$this->assertEquals($row['model1_id'], $id);
		$this->assertEquals($row['is_deleted'], 0);
		
		$model->delete();
		$row = $this->getOneById($model->getModel1Id());
		$this->assertEquals($row['model1_id'], $id);
		$this->assertEquals($row['is_deleted'], 1);
		
		$model->restore();
		$row = $this->getOneById($model->getModel1Id());
		$this->assertEquals($row['model1_id'], $id);
		$this->assertEquals($row['is_deleted'], 0);
	}
	
	public function testGetSoftDeletedObject()
	{
		$model1 = $this->getModel();
		$model1->disableSoftDelete();

		$all1 = $model1->getAll();
		
		$model = $this->createModelInstance();
		$all2 = $model1->getAll();
		
		$model->delete();
		$all3 = $model1->getAll();
		
		//we can fetch soft-deleted objects
		$this->assertEquals(count($all3), count($all2));
		
		//we can fetch soft-deleted object
		$this->assertNotNull($model1->getOne(array('model1_id' => $model->getModel1Id())));
		
		//we can count soft-deleted objects
		$count = $model1->count();
		$this->assertEquals($count, count($all2));
	}

	
	public function testLoadSoftDeletedItem()
	{
		$model = $this->createModelInstance();
		$model->delete();
		
		$this->assertNotNull($model->loadSoftDeletedItem($model->getModel1Id()));
	}
	
	public function testLoadSoftDeletedInstance_DeleteAndCreateAgainTheSameObject()
	{
		//relationship between model2 - model 1, manage by model 3
		//before create new model12 instance check if a soft-deleted one exist
		
		//create an model12 instace then soft delete
		$model1 = $this->createModelInstance();
		
		$table2 = new SampleModel\Model2Table($this->dbAdapter);
		$model2 = new SampleModel\Model2();
		$model2->setServiceManager($this->sl);
		$model2->setTable($table2);
		
		$model2->exchangeArray(array(
			'name' => 'test'
		));
		$model2->save();
		
		$table12 = new SampleModel\Model12Table($this->dbAdapter);
		$model12 = new SampleModel\Model12();
		$model12->setServiceManager($this->sl);
		$model12->setTable($table12);
		
		$model12->exchangeArray(array(
			'model1_id' => $model1->getModel1Id(),
			'model2_id' => $model2->getModel2Id(),
		));
		$model12->save();
		$model12->delete();
		
		$table12ForCheck = new TableGateway('model12table', $this->dbAdapter);
		$count1 = $table12ForCheck->select()->count();
		
		//create another model12 instance doesn't create new record but will reuse soft-deleted instance
		$anotherTable12 = new SampleModel\Model12Table($this->dbAdapter);
		$anotherModel12 = new SampleModel\Model12();
		$anotherModel12->setServiceManager($this->sl);
		$anotherModel12->setTable($anotherTable12);
		$anotherModel12->exchangeArray(array(
			'model1_id' => $model1->getModel1Id(),
			'model2_id' => $model2->getModel2Id(),
		));
		$anotherModel12->save();
		
		$count2 = $table12ForCheck->select()->count();
		
		//during save $anotherModel12, we just reset flag for soft-deleted instance so no more record in database.
		$this->assertEquals($count1, $count2);
	}
}
