<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './middleware/UserMiddleware.php';

// Instantiate App
$app = AppFactory::create();
$app->setBasePath("/app");
$app->addRoutingMiddleware();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
	$group->get('[/]', \UsuarioController::class . ':TraerTodos');
	$group->post('[/]', \UsuarioController::class . ':CargarUno')->add(\UserMiddleware::class . ':ValidarSocio');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
	$group->get('[/]', \MesaController::class . ':TraerTodos');
	$group->post('[/]', \MesaController::class . ':CargarUno')->add(\UserMiddleware::class . ':ValidarSocio');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
	$group->get('[/]', \ProductoController::class . ':TraerTodos');
	$group->post('[/]', \ProductoController::class . ':CargarUno')->add(\UserMiddleware::class . ':ValidarSocio');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
	$group->get('[/]', \PedidoController::class . ':TraerTodos');
	$group->post('[/]', \PedidoController::class . ':CargarUno')->add(\UserMiddleware::class . ':ValidarMozo');
});

$app->group('/csv', function (RouteCollectorProxy $group) {
	$group->post('[/]', \ProductoController::class . ':GuardarProductos');
	$group->put('[/]', \ProductoController::class . ':CargarProductos');
});

$app->run();