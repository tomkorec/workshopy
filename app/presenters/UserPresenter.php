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
    
    public function createComponentRegistrationConfirm() {
        $form = new Form;
        $form->addText('code','Kód');
        $form->addSubmit('send','Potvrdit');
        $form->onSuccess[] = [$this,'confirmRegistration'];
        return $form;
    }
    
    public function confirmRegistration(Form $form, $values){
        $currentUser = $this->database->table('users')->where('registration_code',$values->code)->fetch();
        if($currentUser){
            $currentUser->update([
            'active' => 1,
        ]);
        $this->flashMessage('Váš účet byl úspěšně aktivován! Nyní se můžete přihlásit');
        $this->redirect('Sign:in');
        }else{
            $this->flashMessage('Registrační kód byl chybně zadán','error');
        }
        
    }

    public function renderDefault(){
        
    }
    
    
    
}
