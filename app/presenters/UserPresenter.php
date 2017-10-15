<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

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

    protected function createComponentChangePassword()
    {
        $form = new Form;
        $form->addPassword('oldPassword','Staré heslo')
            ->setRequired('Zadejte prosím staré heslo');
        $form->addPassword('newPassword','Nové heslo')
            ->setRequired('Zadejte prosím nové heslo');
        $form->addPassword('newRepeat','Nové heslo znovu')
            ->setRequired('Zadejte prosím nové heslo ještě jednounet');
        $form->addSubmit('send','Změnit heslo');
        $form->onSuccess[] = [$this,'changePassword'];
        return $form;
    }

    public function changePassword($form, $values){
        $user = $this->database->table('users')->get($this->user->id);
        if(!Passwords::verify($values->oldPassword,$user['password'])){
            $this->flashMessage('Špatně zadané původní heslo.','error');
            $this->redirect('this');
        }
        elseif (Passwords::verify($values->oldPassword,$user['password'])){
            if($values->newPassword == $values->newRepeat) {
                $user->update([
                    'password' => Passwords::hash($values->newPassword),
                ]);
                $this->flashMessage('Heslo bylo úspěšně změněno.');
                $this->redirect('Homepage:');
            } else {
                $this->flashMessage('Nová hesla se neshodují!','error');
                $this->redirect('this');
            }
        }

    }

    public function renderPassword(){

    }

    public function renderDefault(){
        
    }
    
    
    
}
