<?php

use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get("/administrator/orders/:idorder/status", function ($idorder) {
    User::verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);

    $page = new PageAdmin();

    $page->setTpl("order-status", [
        'order' => $order->getValues(),
        'status' => OrderStatus::listAll(),
        'msgSuccess' => Order::getSuccess(),
        'msgError' => Order::getError()
    ]);
});

$app->post("/administrator/orders/:idorder/status", function ($idorder) {
    User::verifyLogin();

    if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
        Order::setError("Informe o status atual.");
        header("Location: /administrator/orders/" . $idorder . "/status");
        exit;
    }

    $order = new Order();

    $order->get((int)$idorder);

    $order->setidstatus((int)$_POST['idstatus']);

    $order->save();

    Order::setSuccess("Status atualizado.");
    header("Location: /administrator/orders/" . $idorder . "/status");
    exit;
});

$app->get("/administrator/orders/:idorder/delete", function ($idorder) {
    User::verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);

    $order->delete();

    header("Location: /administrator/orders");
    exit;
});

$app->get("/administrator/orders/:idorder", function ($idorder) {
    User::verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);

    $cart = $order->getCart();

    $page = new PageAdmin();

    $page->setTpl("order", [
        'order' => $order->getValues(),
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts()
    ]);
});

$app->get("/administrator/orders", function () {

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("orders", [
        "orders" => Order::listAll()
    ]);
});
