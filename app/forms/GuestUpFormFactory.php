<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model;


class GuestUpFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\WorkshopManager*/
	private $workshopManager;

	public function __construct(FormFactory $factory, Model\WorkshopManager $workshopManager)
	{
		$this->factory = $factory;
		$this->workshopManager = $workshopManager;
	}


	/**
	 * @return Form
	 */
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addCheckbox('workshop','Workshop', [
			'cz' => 'ČR',
			'sl' => 'SR',
		]);
		$form->addSubmit('send','send');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess){
			
			$this->workshopManager->wshopFormSuccess($form, $values);
			$onSuccess();
		};

		return $form;
	}
}
