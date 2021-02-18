<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page(); //Atribui a classe Page ao objeto page [obrigatória a criação da stancia]; [também chama o método construtor, responsável por exibir o header na página]

	$page->setTpl("index"); //Carrega o arquivo index que contém o conteúdo da página
//-----------------------------------------------------------------------------------//
	//Após tudo ter sido realizado, a function construct é zerada da memória, com isso a function destruct é invocada, contruindo o header 
//-----------------------------------------------------------------------------------//
});// após todo esses processos terem sido realizados dentro da rota / utilizada pelo framework slimframework.

$app->get('/administrator', function() {
    
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");// ("index") = template
});

$app->get('/administrator/login', function() {

	$page = new PageAdmin([
			"header"=>false,//desabilita o header padrão que é chamado pelo método construct, na page do usuário normal
			"footer"=>false //desabilita o footer padrão que é chamado pelo método destruct na page do usuário normal
	]);

	$page->setTpl("login");// ("index") = template
});

$app->post('/administrator/login', function() {

	User::login($_POST["login"], $_POST["password"]);

	header("Location /administrator");
	exit;

});

$app->get('/administrator/logout', function(){
	User::logout();

	header("Location: /administrator/login");
	exit;
});

$app->run();// run executa todo esse pacote que foi construído dentro da rota, exibindo o conteúdo na tela.

 ?>