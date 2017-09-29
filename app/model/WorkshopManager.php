<?php

namespace App\Model;

use Nette;


/**
 * Workshop management.
 */
class WorkshopManager
{
	use Nette\SmartObject;

	
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function wshopFormSuccess($form, $values){
		echo "string";
		return;
	}
	
}