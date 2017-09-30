<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use App\Model;
use Nette\Application\UI\Form;

class UserPresenter extends BasePresenter
{


	/** @var Nette\Database\Context */
	private $database;

	/** @var Model\WorkshopManager */
	private $workshopManager;

	public function __construct(Nette\Database\Context $database, Model\WorkshopManager $workshopManager)
    {
        $this->database = $database;
        $this->workshopManager = $workshopManager;
    }
    

    
    
}
