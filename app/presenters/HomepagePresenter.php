<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use App\Model;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
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

    protected function createComponentGuestUpForm($workshopManager)
	{
		$form = new Form;
		$workshopManager = $this->workshopManager;
		$form->addHidden('occupancy','obsazenost');
                $form->addHidden('capacity','kapacita');
                $form->addSubmit('send','Závazně se zapsat');
		$form->onSuccess[] = [$this, 'guestUpFormSuccess'];
		return $form;
	}

	public function guestUpFormSuccess(Form $form,$values){
		$workshopIds = $form->getHttpData($form::DATA_TEXT | $form::DATA_KEYS, 'sel[]');
                $workshops = $this->database->table('workshops');
                $entries = $this->database->table('entries');
                if($this->user->isLoggedIn()){
                    $userId = $this->user->id;
                    
                    foreach ($workshopIds as $wId){ //srovná nové přírůstky se stávajícími workshopy
                        $newWorkshop = $this->database->table('workshops')->get($wId);
                        $workshopEntries = $entries->where('user_id',$userId);
                        foreach ($workshopEntries as $workshopEntry){
                            $currentWorkshop = $workshops->get($workshopEntry->workshop_id);
                            $result = $this->workshopManager->checkNewOverlap($newWorkshop, $currentWorkshop);
                            if($result > 0){
                                $this->flashMessage("Časy workshopů ".$newWorkshop->name." a ".$currentWorkshop->name." se překrývají","error");
                                $this->redirect('Homepage:');
                                break;
                            }
                        }
                    }
                    
                    
                    foreach ($workshopIds as $workshopId){
                        if($values->capacity <= $values->occupancy){    //check for occupancy limit
                            $this->flashMessage('Kapacita workshopu byla vyčerpána.','error');
                            $this->redirect('Homepage:');
                            break;
                        }
                        $entries = $this->database->table('entries');
                        $checkEntry = 0;
                        foreach($entries as $entry){
                            if($entry->user_id == $this->user->id && $entry->workshop_id == $workshopId){   //jde o tentýž wshop?
                                $checkEntry++;
                                
                            }
                        }
                        if($checkEntry > 0){   //check for user already signed
                            $this->flashMessage('Nelze se zapsat do workshopu, který máte již zapsaný.','error');
                            $this->redirect('Homepage:');
                            break;
                        }
                        $this->database->table('entries')->insert([
                        'user_id' => $userId,
                        'workshop_id' => $workshopId,
                    ]);
                        $occupancy = $this->database->table('workshops')->get($workshopId)->occupancy;
                        $occupancy++;
                        
                        $this->database->table('workshops')->get($workshopId)->update([
                        'occupancy' => $occupancy,
                    ]);
                    }
                    $this->flashMessage('Byli jste úspěšně přihlášeni na workshopy','success');
                }
                else {
                    $this->error('Nelze provést - uživatel není přihlášen');
                }
		$this->redirect('Homepage:');
        }

	public function renderDefault()
	{
		$this->template->workshops = $this->database->table('workshops');
                $this->template->entries = $this->database->table('entries');
	}

        public function renderOverview() {
            
            $workshops = $this->database->table('workshops');
            $entries = $this->database->table('entries');
            //!spočítat přehledy
            
            //spočítat celkovou částku
            $currentPrice = 0;
            foreach ($workshops as $workshop){
                foreach ($entries as $entry){
                    if($workshop->id == $entry->workshop_id
                            && $this->user->id == $entry->user_id){
                        $currentPrice += $workshop->price;
                    }
                }
            }
            
            $this->template->currentPrice  = $currentPrice;
            $this->template->workshops = $workshops;
            $this->template->entries = $entries;
        }
}
