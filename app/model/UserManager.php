<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;


/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_EMAIL = 'email',
		COLUMN_ROLE = 'role';


	/** @var Nette\Database\Context */
	private $database;
        
        /** @var Nette\Mail\IMailer @inject */
        public $mailer;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)
			->where(self::COLUMN_NAME, $username)
			->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Uživatelské jméno nebo heslo je chybné.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('Uživatelské jméno nebo heslo je chybné.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update([
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			]);
		} elseif (!$row->active){
                    throw new Nette\Security\AuthenticationException('Uživatelské jméno'
                            . 'propojené s tímto účtem nebylo dosud aktivováno.', self::INVALID_CREDENTIAL);
                }

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws DuplicateNameException
	 */
	public function add($username, $email, $password)
	{
		try {
			
                        $this->database->table(self::TABLE_NAME)->insert([
				self::COLUMN_NAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
				self::COLUMN_EMAIL => $email,
			]);
                        $this->sendRegMail($username,$email);
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}
        
        public function sendRegMail($username,$email){
            $mail = new Message;
            $registrationCode = $this->generateRecovery();
            $userIdentity = $this->database->table('users')->where('username',$username)->fetch();
            
            $userIdentity->update([
                'registration_code' => $registrationCode,
            ]);
            $mail->setFrom('noreply@cesta-ven.cz')
                    ->addTo($email)
                    ->setSubject('Registrace na Workshopy | Dudlík fest 2017')
                    ->setHtmlBody('Dobrý den,<br>'
                            . 'pro dokončení registrace navštivte adresu'
                            . ' http://workshopy.cesta-ven.cz/user/registration?'.$registrationCode.'.'
                            . ' <br><br>S pozdravem,<br>'
                            . 'team Dudlík fest');
            $mailer = new SendmailMailer;
            //die();
            //var_dump($mail);
            $mailer->send($mail);
        }

                public function generateRecovery($length = 15){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}



class DuplicateNameException extends \Exception
{
}
