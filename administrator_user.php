<?php

use \Hcode\PageAdmin; // [C:\e-commerce\vendor\hcodebr\php-classes\src\PageAdmin.php]
use \Hcode\Model\User; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\User.php]

// lista todos os usuários 
$app->get("/administrator/users", function () {


	User::verifyLogin(); // vusuário precisa estar logado

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {
		$pagination = User::getPageSearch($search, $page); // $users recebe lista com todos os usuários

	} else {
		$pagination = User::getPage($page); // $users recebe lista com todos os usuários

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++) {
		array_push($pages, [
			'href' => '/administrator/users?' . http_build_query([
				'page' => $x + 1,
				'search' => $search
			]),
			'text' => $x + 1
		]);
	}

	$page = new PageAdmin();

	$page->setTpl("users", array( //seta os dados contidos na variável $users [line 65]
		"users" => $pagination['data'],
		"search" => $search,
		"pages" => $pages
	));
});

// tela para criar usuário
$app->get("/administrator/users/create", function () {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create"); // nome do template chamado

});

//rota para deletar usuários
$app->get("/administrator/users/:iduser/delete", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /administrator/users");
	exit;
});

// tela para editar o usuário
// OBS[Somente de ser inserdo uma parâmetro na rota(Ex.":iduser"), o slin framework entende que a função pode enchergar esse parâmetro, setando ele na função]
$app->get("/administrator/users/:iduser", function ($iduser) { // com o parâmetro iduser, consegue trazer os dados de um usuário específico, para realaizar a edição.

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user" => $user->getValues()
	));
});
// rota para inserir o usuário
$app->post("/administrator/users/create", function () {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$_POST['despassword'] = User::getPasswordHash($_POST['despassword']);

	$user->setData($_POST); //seta os dados dinamicamente

	$user->save();

	header("Location: /administrator/users");
	exit;
});
// rota para inserir/salvar a edição do usuário
$app->post("/administrator/users/:iduser", function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST); //cria varios set's dinamicamente

	$user->update();

	header("Location: /administrator/users");
	exit;
});
