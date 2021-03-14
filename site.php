<?php 

use \Hcode\Page; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Page.php]
use \Hcode\Model\Product; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Product.php]
use \Hcode\Model\Category; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Category.php]

$app->get('/', function() {
    
    $products = Product::listAll();

	$page = new Page(); //Atribui a classe Page ao objeto page [obrigatória a criação da stancia]; [também chama o método construtor, responsável por exibir o header na página]

	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]); //Carrega o arquivo index que contém o conteúdo da página
//-----------------------------------------------------------------------------------//
	//Após tudo ter sido realizado, a function construct é zerada da memória, com isso a function destruct é invocada, contruindo o header 
//-----------------------------------------------------------------------------------//
});// após todo esses processos terem sido realizados dentro da rota / utilizada pelo framework slimframework.


$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i = 1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);

});

 ?>