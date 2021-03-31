<?php 

use \Hcode\Page; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Page.php]
use \Hcode\Model\Product; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Product.php]
use \Hcode\Model\Category; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Category.php]
use \Hcode\Model\Cart; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Cart.php]
use \Hcode\Model\Address; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\Address.php]
use \Hcode\Model\User; // [C:\e-commerce\vendor\hcodebr\php-classes\src\Model\User.php]

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

$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page;

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});

$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for($i = 0; $i < $qtd; $i++) {
		
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;

});

$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});

$app->get("/checkout", function(){

	User::verifyLogin(false);// false define que não é uma rota administrativa

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);

});

$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']//se a sessão conter os dados de registros, irá enviar os dados novamente para os campos no template, se não envia um array com dados vazios.
	]);

});

$app->post("/login", function(){

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e){

		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

$app->get("/logout", function(){

	User::logout();

	header("Location: /login");
	exit;

});

$app->post("/register", function(){

	$_SESSION['registerValues'] = $_POST;//Adiciono os dados do post na sessão, para n serem perdidos quando a página reiniciar, e envio eles novamente para os campos através da rota de login.

	if(!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha o seu email.");
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}

	if(User::checkLoginExist($_POST['email']) === true){

		User::setErrorRegister("Este endereço de email já está sendo utilizado por outro usuário.");
		header("Location: /login");
		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']); //Autentica o usuário assim q ele se cadastrar, já o mantendo logado no site.

	header("Location: /checkout");
	exit;

});

 ?>