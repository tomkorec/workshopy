<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;


class AdminPresenter extends BasePresenter {
    /** @var Nette\Database\Context */
    private $database;
    
    
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }
    
    public function renderDefault(){
        $workshops = $this->database->table('workshops');
        $entries = $this->database->table('entries');
        $users = $this->database->table('users');
        
        $this->template->workshops = $workshops;
        $this->template->entries = $entries;
        $this->template->users = $users;
        
        $wCount = $workshops->count();
        $eCount = $entries->count();
        $uCount = $users->count();
        
        $this->template->wCount = $wCount;
        $this->template->eCount = $eCount;
        $this->template->uCount = $uCount;
    }
    
}
