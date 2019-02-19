<?php
namespace AppCore\Mvc\Model;

/* 
 * SoftDeleteableInterface
 */

interface SoftDeleteableInterface
{
	/**
	 * Whether Soft-delete mechanism is on.
	 * When it's on, delete an instance only mark it's state as deleted.
	 *		Soft-deleted object will not be check with methods like getAll, getOne, count, update 
	 *		as they consider it delete.
	 */
	public function getSoftDeleteable();
	
	/**
	 * Enable soft-delete mechanism
	 */
    public function enableSoftDelete();

	/**
	 * Disable soft-delete mechanism
	 */
    public function disableSoftDelete();
	
	/**
	 * Delete permanently an instance
	 */
	public function hardDelete();
	
	/**
	 * Restore a soft-deleted instance
	 */
	public function restore();
	
}