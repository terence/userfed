<?php
/* 
 * Test for auto update create time, update time for each model
 */

namespace AppCoreTest\Mvc\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;

class CreateUpdateTimeModelTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var AppCoreTest\Mvc\Model\Model
	 */
	public $model;
	public $dbAdapter;
	public $sl;
	
	protected $updatedTimeColumn = 'last_updated';
	protected $createdTimeColumn = 'creation_date';
	
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
	
	/**
	 * Check if a time is current time
	 */
	protected function assertCurrentTime($checkedTime)
	{
		$this->assertNotEmpty($checkedTime);
		//check if created_time is a time and it should be older than now
		$this->assertTrue(strtotime($checkedTime) <= time());
		$this->assertTrue(strtotime($checkedTime) >= strtotime('-2 seconds'));		
	}
	
	public function testCreatedTimeWithAutoIncrementId()
	{
		$model = $this->createModelInstance();
		$row = $this->getOneById($model->getModel1Id());
		
		$this->assertCurrentTime($row[$this->createdTimeColumn]);
		$this->assertCurrentTime($row[$this->updatedTimeColumn]);
	}
	
	public function testCreatedTimeWithProvidedId()
	{
		//delete all record so there is no id clash
		$this->model1TableGateway->delete(array());
		$model = $this->getModel();
		$model->exchangeArray(array(
				'name' => 'test',
				'model1_id' => rand(100,200)
			))
			->save();
		
		$row = $this->getOneById($model->getModel1Id());
		$this->assertCurrentTime($row[$this->createdTimeColumn]);
		$this->assertCurrentTime($row[$this->updatedTimeColumn]);
	}

	public function testUpdatedTime()
	{
		$model = $this->createModelInstance();
		
		$row = $this->getOneById($model->getModel1Id());
		$createTime1 = $row[$this->createdTimeColumn];
		$updateTime1 = $row[$this->updatedTimeColumn];
		$model->setName('test1')->save();
		
		$row = $this->getOneById($model->getModel1Id());
		$createTime2 = $row[$this->createdTimeColumn];
		$updateTime2 = $row[$this->updatedTimeColumn];
		
		$this->assertEquals($createTime1, $createTime2);		
		$this->assertCurrentTime($updateTime2);
	}
	
	public function testUpdatedTimeFromSoftDelete()
	{
		$model = $this->createModelInstance();
		$model->delete();
		
		$row = $this->getOneById($model->getModel1Id());
		$this->assertCurrentTime($row[$this->updatedTimeColumn]);
	}
	
	public function testUpdatedTimeFromRestore()
	{
		$model = $this->createModelInstance();
		$model->delete();
		
		$row = $this->getOneById($model->getModel1Id());
		$updateTime1 = $row[$this->updatedTimeColumn];
		
		$model->restore();
		$row = $this->getOneById($model->getModel1Id());
		$updateTime2 = $row[$this->updatedTimeColumn];
		$this->assertCurrentTime($updateTime2);
		$this->assertTrue(strtotime($updateTime1) <= strtotime($updateTime2));
	}
	
	public function testGetAllByUpdateTimeOrder()
	{
		//remove all data so we check order easier
		$this->model1TableGateway->delete(array());
		
		$model1 = $this->createModelInstance();
		
		//sleep to make sure model2' update time > model 1's update time
		sleep(1);
		
		$model2 = $this->createModelInstance();
		
		$model = $this->getModel();
		//default is order by update time DESC
		$items = $model->getAll();

		//the last insert item will be get first
		$model22 = $items->current();
		$this->assertEquals($model2->getModel1Id(), $model22->getModel1Id());
		
		$items->next();
		$model11 = $items->current();
		$this->assertEquals($model1->getModel1Id(), $model11->getModel1Id());
	}
}
