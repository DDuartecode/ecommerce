<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

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
    
	$page = new PageAdmin();

	$page->setTpl("index");
});

$app->run();// run executa todo esse pacote que foi construído dentro da rota, exibindo o conteúdo na tela.

 ?>