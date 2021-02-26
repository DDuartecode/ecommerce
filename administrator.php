<?php 

use \Hcode\PageAdmin; // [C:\e-commerce\vendor\hcodebr\php-classes\src\PageAdmin.php]
use \Hcode\Model\User;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\User.php]
use \Hcode\Model\Category;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Category.php]

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

 ?>