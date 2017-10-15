<?php

namespace App\Presenters;

use App\Forms;
use Nette;
use Nette\Mail\Message;
use Nette\Application\UI\Form;
use Nette\Mail\SendmailMailer;
use App\Model;
use Nette\Security\Passwords;


class SignPresenter extends BasePresenter
{
	/** @var Forms\SignInFormFactory */
	private $signInFactory;

	/** @var Forms\SignUpFormFactory */
	private $signUpFactory;

    /** @var Forms\SignNewFormFactory */
    private $signNewFactory;

    /** @var Nette\Database\Context */
    private $database;

    /** $var Model\UserManager */
    private $userManager;


	public function __construct(Forms\SignInFormFactory $signInFactory, Forms\SignUpFormFactory $signUpFactory,Forms\SignNewFormFactory $signNewFactory, Model\UserManager $userManager, Nette\Database\Context $database)
	{
		$this->signInFactory = $signInFactory;
		$this->signUpFactory = $signUpFactory;
        $this->signNewFactory = $signNewFactory;
        $this->userManager= $userManager;
        $this->database = $database;
	}


	/**
	 * Sign-in form factory.
	 * @return Form
	 */
	protected function createComponentSignInForm()
	{
		return $this->signInFactory->create(function () {
                        $this->redirect('Homepage:');
		});
	}


	/**
	 * Sign-up form factory.
	 * @return Form
	 */
	protected function createComponentSignUpForm()
	{
		return $this->signUpFactory->create(function () {
                        $this->flashMessage('Aktivační email byl zaslán na Vámi zadanou adresu.','success');
                        $this->redirect('Homepage:');
		});
	}

    protected function createComponentSignNewForm()
    {
        return $this->signNewFactory->create(function () {
            $this->flashMessage('Potvrzovací email byl zaslán na Vámi zadanou adresu.','success');
            $this->redirect('Homepage:');
        });
    }


	public function actionOut()
	{
		$this->getUser()->logout(true);
                $this->flashMessage('Byli jste úspěšně odhlášeni','info');
                $this->redirect('Sign:in');
	}

	protected function createComponentForgottenPassword()
    {
        $form = new Form;
        $form->addEmail('email','Email pro obnovu')
            ->setRequired('Zadejte prosím Váš email');
        $form->addSubmit('send','Obnovit heslo');
        $form->onSuccess[] = [$this,'sendMailPassword'];
        return $form;
    }

    public function sendMailPassword($form, $values){
        $mail = new Message;
        $registrationCode = $this->userManager->generateRandomString();
        $userIdentity = $this->database->table('users')->where('email',$values->email)->fetch();

        $userIdentity->update([
            'registration_code' => $registrationCode,
        ]);
        $mail->setFrom('noreply@cesta-ven.cz')
            ->addTo($values->email)
            ->setSubject('Obnova hesla | Dudlík fest 2017')
            ->setHtmlBody('Vážený uživateli,<br>'
                . '<br>Vaše heslo obnovíte kliknutím na následující odkaz:<br>'
                . '<br>'
                . '<a href="http://workshopy.cesta-ven.cz/sign/restore?code='.$registrationCode.'">'
                . 'http://workshopy.cesta-ven.cz/user/sign?code='.$registrationCode.'</a>,<br>'
                . '<br>S pozdravem,<br>'
                . 'team Dudlík fest');
        $mailer = new SendmailMailer;
        //die();
        //var_dump($mail);
        $mailer->send($mail);
        $this->flashMessage('Zkontrolujte prosím Vaši emailovou schránku');
        $this->redirect('Homepage:default');
    }

    protected function createComponentRestorePassword()
    {
        $form = new Form;
        $form->addHidden('restoreCode','restoreCode');
        $form->addPassword('newPassword')
            ->setRequired();
        $form->addPassword('newRepeat')
            ->setRequired();
        $form->addSubmit('send','OBNOVIT HESLO');
        $form->onSuccess[] = [$this,'restorePassword'];
        return $form;
    }

    public function restorePassword($form,$values){
        $user = $this->database->table('users')
            ->where('registration_code',$values->restoreCode)
            ->fetch();
        if($user){
            if($values->newPassword == $values->newRepeat) {
                $rString = $this->userManager->generateRandomString();
                $user->update([
                    'password' => Passwords::hash($values->newPassword),
                    'registration_code' => $rString,
                ]);

                $this->flashMessage('Heslo bylo úspěšně změněno.');
                $this->redirect('Homepage:');
            } else {
                $this->flashMessage('Nová hesla se neshodují!', 'error');
                $this->redirect('this', array('code' => $values->restoreCode));
            }
        } else {
            $this->flashMessage('Chybný nebo zastaralý odkaz pro obnovu!', 'error');
            $this->redirect('Homepage:');
        }
    }
}
