<?php
session_start(); //os dados da sessão sessão serão inseridos quando o método mágico __call detectar que um método foi acionado, realizando os gets e seters dinamicamente dentro da classe Model - localizada[C:\e-commerce\vendor\hcodebr\php-classes\src\Model.php].
require_once("vendor/autoload.php");

use \Slim\Slim; // [C:\e-commerce\vendor\slim\slim\Slim\Slim.php]

$app = new Slim();

// OBS[Com a utilização de rotas, e a automatização dos gat's e set's, o que diferencia de um dado sera inserido, ou retornado é o método. Se for um get, retorna dados, se for post, insere dados].

$app->config('debug', true); // método do slimframework[true = exibe os exceptions dentro do framework] - [false = não exibe exceptions do framework]

require_once("functions.php");
require_once("site.php");
require_once("administrator.php");
require_once("administrator_user.php");
require_once("administrator_categories.php");
require_once("administrator_products.php");
require_once("administrator-orders.php");
/* MÉTODO SLIMFRAMEWORK*/
$app->run();// run executa todo esse pacote que foi construído dentro da rota, exibindo o conteúdo na tela.
