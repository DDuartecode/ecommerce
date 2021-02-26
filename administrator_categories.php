<?php 

use \Hcode\PageAdmin; // [C:\e-commerce\vendor\hcodebr\php-classes\src\PageAdmin.php]
use \Hcode\Model\User;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\User.php]
use \Hcode\Model\Category;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Category.php]

$app->get("/administrator/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		'categories'=>$categories
	]);

});

$app->get("/administrator/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

$app->post("/administrator/categories/create", function(){

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /administrator/categories');
	exit;

});

$app->get("/administrator/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /administrator/categories");
	exit;

});

$app->get("/administrator/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);

});

$app->post("/administrator/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header("Location: /administrator/categories");
	exit;

});

$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});


 ?>