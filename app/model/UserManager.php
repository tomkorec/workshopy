<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Mail\Message;
use Nette\Http\Request;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use Tester\Environment;


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
                COLUMN_FULLNAME = 'name',
		COLUMN_ROLE = 'role';


    /** @var Nette\Http\Request */
    private $httpRequest;

	/** @var Nette\Database\Context */
	private $database;
        
        /** @var Nette\Mail\IMailer @inject */
        public $mailer;

	public function __construct(Nette\Database\Context $database, Nette\Http\Request $httpRequest)
	{
		$this->database = $database;
		$this->httpRequest = $httpRequest;
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

    public function add($username, $email, $password, $name)
    {
        $dbUser = $this->database->table(self::TABLE_NAME);
        try {
            if($dbUser->where('username',$username)->fetch()){
                throw new DuplicateNameException;
            }

            $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_NAME => $username,
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
                self::COLUMN_EMAIL => $email,
                self::COLUMN_FULLNAME => $name,

            ]);

            $this->sendRegMail($username,$email,$password,$name);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {

            throw new DuplicateNameException;
        }
    }

	public function addNew($username, $email, $name, $verificationParam)
	{
	            $dbUsers = $this->database->table(self::TABLE_NAME);
                $generatedPassword = $this->generateRandomString(10);
                try {
                        if($dbUsers->where('username',$username)->fetch()){
                            throw new DuplicateNameException;
                        }
                    $newUser = $this->database->table(self::TABLE_NAME)
                    ->where('verification',$verificationParam)->fetch();

                    $newUser->update([
				self::COLUMN_NAME => $username,
				self::COLUMN_EMAIL => $email,
                self::COLUMN_PASSWORD_HASH => Passwords::hash($generatedPassword),
                self::COLUMN_FULLNAME => $name,
			]);

                    $wEntries= $this->database->table('entries')->where('user_id', $newUser->id);
                    $this->sendNewMail($username,$email,$generatedPassword,$name,$wEntries);
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			
                        throw new DuplicateNameException;
		}
	}

        public function sendNewMail($username,$email,$password,$name,$wEntries){
            $mail = new Message;
            $registrationCode = $this->generateRandomString();
            $userIdentity = $this->database->table('users')->where('username',$username)->fetch();

            $userIdentity->update([
                'registration_code' => $registrationCode,
            ]);
            $mail->setFrom('noreply@cesta-ven.cz')
                ->addTo($email)
                ->setSubject('Přihlášené Workshopy | Dudlík fest 2017')
                ->setHtmlBody('Vážený uživateli '.$name.',<br>'
                    . '<br>byl jste úspěšně přihlášen na workshopy.<br>'
                    . '<br>Pro zobrazení seznamu přihlášených worksopů a přihlášení dalších si můžete aktivovat účet '
                    . 'kliknutím na následující odkaz: <br>'
                    . '<a href="http://workshopy.cesta-ven.cz/user/registration?code='.$registrationCode.'">'
                    . 'http://workshopy.cesta-ven.cz/user/registration?code='.$registrationCode.'</a>,<br>'
                    . 'nebo zadejte na adrese <a href="http://workshopy.cesta-ven.cz/user/registration">'
                    . 'této</a> adrese kód <strong>'.$registrationCode.'</strong><br>'
                    . '<br>Po aktivaci účtu se můžete přihlásit pod uživatelským jménem a heslem.<br>'
                    . '<strong>Uživatelské jméno: </strong>'.$username.'<br>'
                    . '<strong>Heslo: </strong>'.$password.'<br>'
                    . '<br>S pozdravem,<br>'
                    . 'team Dudlík fest');
            $mailer = new SendmailMailer;
            //die();
            //var_dump($mail);
            $mailer->send($mail);
        }

	    public function sendRegMail($username,$email,$password,$name){
            $mail = new Message;
            $registrationCode = $this->generateRandomString();
            $userIdentity = $this->database->table('users')->where('username',$username)->fetch();
            
            $userIdentity->update([
                'registration_code' => $registrationCode,
            ]);
            $mail->setFrom('noreply@cesta-ven.cz')
                    ->addTo($email)
                    ->setSubject('Registrace na Workshopy | Dudlík fest 2017')
                    ->setHtmlBody('Vážený uživateli '.$name.',<br>'
                            . '<br>pro dokončení registrace a aktivaci Vašeho účtu navštivte prosím adresu<br>'
                            . '<a href="http://workshopy.cesta-ven.cz/user/registration?code='.$registrationCode.'">'
                            . 'http://workshopy.cesta-ven.cz/user/registration?code='.$registrationCode.'</a>,<br>'
                            . 'nebo zadejte na adrese <a href="http://workshopy.cesta-ven.cz/user/registration">'
                            . 'této</a> adrese kód <strong>'.$registrationCode.'</strong><br>'
                            . '<br>Po aktivaci účtu se můžete přihlásit pod svým uživatelským jménem a heslem.<br>'
                            . '<strong>Uživatelské jméno: </strong>'.$username.'<br>'
                            . '<strong>Heslo: </strong>'.$password.'<br>'
                            . '<br>S pozdravem,<br>'
                            . 'team Dudlík fest');
            $mailer = new SendmailMailer;
            //die();
            //var_dump($mail);
            $mailer->send($mail);
        }

        public function generateRandomString($length = 15){
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
