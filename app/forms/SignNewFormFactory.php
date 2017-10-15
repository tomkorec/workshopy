<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


class SignNewFormFactory
{
	use Nette\SmartObject;

	const PASSWORD_MIN_LENGTH = 7;

	/** @var FormFactory */
	private $factory;

	/** @var Model\UserManager */
	private $userManager;
        


	public function __construct(FormFactory $factory, Model\UserManager $userManager)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
	}


	/**
	 * @return Form
	 */
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addText('username', 'Zadejte uživatelské jméno:')
			->setRequired('Zvolte si prosím své uživatelské jméno.');
                $form->addText('name','Zadejte Vaše jméno a příjmení:')
                        ->setRequired('Uveďte prosím své jméno a příjmení')
                        ->addRule($form::PATTERN,'Zadejte prosím celé jméno','(.*\s+.*)');
        $form->addHidden('verification','Verifikace');
		$form->addEmail('email', 'Váš e-mail:')
			->setRequired('Prosím zadejte Váš email.');

		$form->addSubmit('send', 'Zaregistrovat');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
                    
                        try {
				$this->userManager->addNew($values->username, $values->email, $values->name, $values->verification);
			} catch (Model\DuplicateNameException $e) {
                                
				$form->addError('Uživatelské jméno je již zabrané. Zvolte prosím jiné.');
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
