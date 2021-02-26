<?php 

use \Hcode\PageAdmin; // [C:\e-commerce\vendor\hcodebr\php-classes\src\PageAdmin.php]
use \Hcode\Model\User;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\User.php]
use \Hcode\Model\Product;// [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Product.php]

$app->get("/administrator/products", function(){

	User::verifyLogin();

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
	]);

});

$app->get("/administrator/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");

});

$app->post("/administrator/products/create", function(){

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /administrator/products");
	exit;
});

$app->get("/administrator/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", [
		'product'=>$product->getValues()
	]);

});

$app->post("/administrator/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header("Location: /administrator/products");
	exit;

});

$app->get("/administrator/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header("Location: /administrator/products");
	exit;

});



 ?>