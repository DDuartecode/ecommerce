<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";//defini o nome da sessão
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

	public static function getFromSession()
	{

		$user = new User();

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
	{
		if(
			!isset($_SESSION[User::SESSION]) // se a sessão não foi definida
			|| 							//ou
			!$_SESSION[User::SESSION] // se a sessão for falsa/vazia
			||							//ou
			!(int)$_SESSION[User::SESSION]["iduser"] > 0// se o id do usuário não for maior que zero
		) {
			//Não está logado
			return false;
		} else {
			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				return true;

			} else if($inadmin === false) {

				return true;

			} else {

				return false;

			}
		}
	}

	public static function login($login, $password) //valida login e senha
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a
								INNER JOIN tb_persons b ON a.idperson = b.idperson
								WHERE deslogin = :LOGIN;", array(
			":LOGIN"=>$login
		));
		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true)
		{

			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();//pega os valores do model e insere na sessão.

			return $user;



		} else {

			throw new \Exception("Usuário inexistente ou senha inválida.");
			
		}
	}

	public static function verifyLogin($inadmin = true)
	{

		if (!User::checkLogin($inadmin)){

			if($inadmin){
				header("Location: /administrator/login");//redireciona para a tela de login administrativo novamente
			} else {
				header("Location: /login");//redireciona para a tela de login do site novamente 
			}
			exit;
		}
	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL; // atribui o valor nulo a sessão, finalizando ela.
	}

	public static function listAll()
	{
		$sql = new Sql();

	 return	$sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

	public function save()
	{

		$sql = new Sql();
		/*
		pdesperson VARCHAR(64),
		pdeslogin VARCHAR(64),
		pdespassword VARCHAR(256),
		pdesemail VARCHAR(128),
		pnrphone BIGINT,
		pinadmin TINYINT
		 */
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$data = $results[0];

		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data); // realiza os set's de acordo com a quantidade de dados
	}

	//atualiza os dados do usuário, quando editado
	public function update()
	{
		$sql = new Sql();
		/*
		pdesperson VARCHAR(64),
		pdeslogin VARCHAR(64),
		pdespassword VARCHAR(256),
		pdesemail VARCHAR(128),
		pnrphone BIGINT,
		pinadmin TINYINT
		 */
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		)); // atualiza todas as informações do usuário que teve o id informado

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}
	//função para recuperação de senha
	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT *
								FROM tb_persons a INNER JOIN tb_users b USING(idperson)
								WHERE a.desemail = :email;", array(
									":email"=>$email
								)); // busca os dados do usuário de acordo com o email inserido.

		//verifica se foi retornado um usuário
		if(count($results) === 0)// se o retorno for igual a zero, email não está cadastrado no banco
		{
			throw new \Exception("Não foi possível recuperar a senha.");	
		}
		else //se for diferente de zero, o email está cadastrado e prossegue com a validação da senha
		{

			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			)); // insere o registro da tentativa de redefinição de senha e retorna os dados desse registro

			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha.");	
			}
			else
			{
				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));//busca o id da solicitação de redefinição de senha e criptografa o mesmo
				$code = base64_encode($code);//codifica em base 64, para tornar legível caracteres ilegíveis, gerando um código.

				if($inadmin === true){
					$link = "http://www.hcodecommerce.com.br/administrator/forgot/reset?code=$code"; //link que será enviado para o email cadastrado, para realizar a alteração de senha.
				} else {
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code"; //link que será enviado para o email cadastrado, para realizar a alteração de senha.
				}

				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();

				return $data;
			}
		}
	}
	//valida os dados para retornar a tela para alterar a senha
	public static function validForgotDecrypt($code)
	{
		$sql = new Sql();

		$code = base64_decode($code);//decodifica os dados ilegíveis, deixando eles da forma que estavam quando foi encryptado do banco.

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV)); // faz o decrypt do código, deixando ele da forma que está no banco

		//busca os dados do usuário de acordo com o código de recuperação enviado
		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		)); // faz um select do usuário referente ao código de recuperação de senha enviado, porém só retorna se o envio do código foi feito dentro do prazo de 1 hr do momento da solicitação e se esse código não foi utilizado para uma recuperação nenhuma vez.

		//valida se o código retornou algo
		if(count($results) ===0)// se a quantidade de linhas for igual a zero
		{
			throw new \Exception("Não foi possível recuperar a senha");// estoura uma excessão
		}
		else // se for diferente de zero
		{
			return $results[0]; // retorna os dados do usuário
		}
	}
	//insere a data da alteração da senha
	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));// faz o update do campo dtrecovery, inserindo a data e hora em que foi alterado a senha com a utilização do código/idrecovery enviado
	}
	//altera a senha no banco
	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));// faz o update da senha para o usuário que teve o id informado

	}

	public static function setError($msg)
	{
		$_SESSION[User::ERROR] = $msg;
	}

	public static function getError()
	{
		$msg = (isset($_SESSION[User::ERROR])) ? $_SESSION[User::ERROR] : "";

		User::clearError();

		return $msg;
	}

	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
	}

	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
	}

	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;
	}

	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}

	public static function checkLoginExist($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results) > 0);

	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

}

 ?>