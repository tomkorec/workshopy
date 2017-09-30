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
        
        public function checkAllOverlaps($validWorkshops){
            $overlapState = array();
            foreach ($validWorkshops as $currentWorkshop){
                foreach ($validWorkshops as $compareWorkshop){
                    if($currentWorkshop->id != $compareWorkshop->id)
                        $checkOverlapState = $this->checkTimeOverlap($currentWorkshop->time_from, $currentWorkshop->time_to, $compareWorkshop->time_from, $compareWorkshop->time_to);
                        array_push ($overlapState, $checkOverlapState);
                }
            }
            return $overlapState;
        }
        
        public function checkNewOverlap($newWorkshop,$currentWorkshop){
                       
            $checkOverlapState = $this->checkTimeOverlap($currentWorkshop->time_from, $currentWorkshop->time_to, $newWorkshop->time_from, $newWorkshop->time_to);
            
            if($checkOverlapState > 0){
                return 1;
            }
            else {
                return 0;
            }
        }

                public  function checkTimeOverlap($start_date_last, $end_date_last, $start_date_next, $end_date_next){
            if($start_date_last <= $end_date_next && $end_date_last >= $end_date_next){
                return 1;
            }
            else {
                
                return 0;
            }
        }
	
}