<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


class SignUpFormFactory
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

		$form->addEmail('email', 'Váš e-mail:')
			->setRequired('Prosím zadejte Váš email.');

		$form->addPassword('password', 'Vytvořte heslo:')
			->setOption('description', sprintf('alespoň %d znaků', self::PASSWORD_MIN_LENGTH))
			->setRequired('Prosím vytvořte heslo.')
			->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

		$form->addSubmit('send', 'Zaregistrovat');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->userManager->add($values->username, $values->email, $values->password);
			} catch (Model\DuplicateNameException $e) {
				$form['username']->addError('Uživatelské jméno je již zabrané. Zvolte prosím jiné.');
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
