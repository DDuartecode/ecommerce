<?php 
session_start(); //os dados da sessão sessão serão inseridos quando o método mágico __call detectar que um método foi acionado, realizando os gets e seters dinamicamente dentro da classe Model - localizada[C:\e-commerce\vendor\hcodebr\php-classes\src\Model.php].
require_once("vendor/autoload.php");

use \Slim\Slim;// [C:\e-commerce\vendor\slim\slim\Slim\Slim.php]
use \Hcode\Page; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Page.php]
use \Hcode\PageAdmin; // [C:\e-commerce\vendor\hcodebr\php-classes\src\PageAdmin.php]
use \Hcode\Model\User;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\User.php]

$app = new Slim();

// OBS[Com a utilização de rotas, e a automatização dos gat's e set's, o que diferencia de um dado sera inserido, ou retornado é o método. Se for um get, retorna dados, se for post, insere dados].

$app->config('debug', true);// método do slimframework[true = exibe os exceptions dentro do framework] - [false = não exibe exceptions do framework]

$app->get('/', function() {
    
	$page = new Page(); //Atribui a classe Page ao objeto page [obrigatória a criação da stancia]; [também chama o método construtor, responsável por exibir o header na página]

	$page->setTpl("index"); //Carrega o arquivo index que contém o conteúdo da página
//-----------------------------------------------------------------------------------//
	//Após tudo ter sido realizado, a function construct é zerada da memória, com isso a function destruct é invocada, contruindo o header 
//-----------------------------------------------------------------------------------//
});// após todo esses processos terem sido realizados dentro da rota / utilizada pelo framework slimframework.

$app->get('/administrator', function() { // direciona para página de administrador
    
	User::verifyLogin(); // verifica se o usuário está logado

	$page = new PageAdmin();

	$page->setTpl("index");// ("index") = template
});

$app->get('/administrator/login', function() {

	$page = new PageAdmin([
			"header"=>false,//desabilita o header padrão que é chamado pelo método construct, na page do usuário normal
			"footer"=>false //desabilita o footer padrão que é chamado pelo método destruct na page do usuário normal
	]);

	$page->setTpl("login");// ("login") = template
});

$app->post('/administrator/login', function() {

	User::login($_POST["login"], $_POST["password"]); // passa os dados enviados pelo formulário via post, para validar login e senha.
// apos concluído a função
	header("Location: /administrator");// redireciona para a pagina do administrador
	exit;//finaliza a chamada

});

$app->get('/administrator/logout', function(){
	User::logout(); // finaliza a sessão

	header("Location: /administrator/login");
	exit; // finaliza a chamada
});

// lista todos os usuários 
$app->get("/administrator/users", function(){ 


	User::verifyLogin(); // vusuário precisa estar logado

	$users = User::listAll(); // $users recebe lista com todos os usuários

	$page = new PageAdmin();

	$page->setTpl("users", array( //seta os dados contidos na variável $users [line 65]
		"users"=>$users
	));

});

// tela para criar usuário
$app->get("/administrator/users/create", function(){ 

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create"); // nome do template chamado

});

//rota para deletar usuários
$app->get("/administrator/users/:iduser/delete", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /administrator/users");
	exit;

});

// tela para editar o usuário
// OBS[Somente de ser inserdo uma parâmetro na rota(Ex.":iduser"), o slin framework entende que a função pode enchergar esse parâmetro, setando ele na função]
$app->get("/administrator/users/:iduser", function($iduser){ // com o parâmetro iduser, consegue trazer os dados de um usuário específico, para realaizar a edição.

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});
// rota para inserir o usuário
$app->post("/administrator/users/create", function(){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

	$user->setData($_POST); //seta os dados dinamicamente

	$user->save();

	header("Location: /administrator/users");
	exit;

});
// rota para inserir/salvar a edição do usuário
$app->post("/administrator/users/:iduser", function($iduser){ 

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST); //cria varios set's dinamicamente

	$user->update();

	header("Location: /administrator/users");
	exit;

}); 

$app->get("/administrator/forgot", function(){//assionado ao clicar no botão

	$page = new PageAdmin([
		"header"=>false,//desabilita o header padrão que é chamado pelo método construct, na page do usuário normal
		"footer"=>false //desabilita o footer padrão que é chamado pelo método destruct na page do usuário normal
	]);

	$page->setTpl("forgot");// ("forgot") = template

});

$app->post("/administrator/forgot", function(){//assionado ao enviar os dados

	User::getForgot($_POST["email"]); //pega o email inserido na página de redefinição de senha. [forgot.html]
	header("Location: /administrator/forgot/sent");
	exit;

});

$app->get("/administrator/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,//desabilita o header padrão que é chamado pelo método construct, na page do usuário normal
		"footer"=>false //desabilita o footer padrão que é chamado pelo método destruct na page do usuário normal
	]);

	$page->setTpl("forgot-sent");// ("forgot-sent") = template

});

$app->get("/administrator/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,//desabilita o header padrão que é chamado pelo método construct, na page do usuário normal
		"footer"=>false //desabilita o footer padrão que é chamado pelo método destruct na page do usuário normal
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));// ("forgot-reset") = template

});

$app->post("/administrator/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,//desabilita o header padrão que é chamado pelo método construct, na page do usuário normal
		"footer"=>false //desabilita o footer padrão que é chamado pelo método destruct na page do usuário normal
	]);

	$page->setTpl("forgot-reset-success");// ("forgot-reset-success") = template

});

/* MÉTODO SLIMFRAMEWORK*/
$app->run();// run executa todo esse pacote que foi construído dentro da rota, exibindo o conteúdo na tela.

 ?>