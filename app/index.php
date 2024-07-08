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
require_once './middleWare/VerificarParametros.php';
require_once './controllers/LoggerController.php';
require_once './controllers/EncuestaController.php';


session_start();

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
	$group->post('[/]', \UsuarioController::class . ':CargarUno')->add(new VerificarParametros(["tipo", "usuario", "clave"], "POST", false))->add(new UserMiddleware(["Socio"]));
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
	$group->get('[/]', \MesaController::class . ':TraerTodos');
	$group->post('[/]', \MesaController::class . ':CargarUno')->add(new UserMiddleware(["Socio"]));
	$group->post('/cerrar', \MesaController::class . ':CerrarMesa')->add(new VerificarParametros(["codigo"], "POST", false))->add(new UserMiddleware(["Socio"]));
	$group->get('/popular', \MesaController::class . ':MesaMasUsada')->add(new UserMiddleware(["Socio"]));
});

$app->group('/productos', function (RouteCollectorProxy $group) {
	$group->get('[/]', \ProductoController::class . ':TraerTodos');
	$group->post('[/]', \ProductoController::class . ':CargarUno')->add(new VerificarParametros(["tipo", "nombre", "precio"], "POST", false))->add(new UserMiddleware(["Socio"]));
	$group->get('/pdf', \ProductoController::class . ':DescargarPDF');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
	$group->get('[/]', \PedidoController::class . ':TraerTodos');
	$group->post('[/]', \PedidoController::class . ':CargarUno')->add(new VerificarParametros(["producto", "cliente", "mesa"], "POST", false))->add(new UserMiddleware(["Socio", "Mozo"]));
	$group->post('/adjuntar', \PedidoController::class . ':SubirFoto')->add(new VerificarParametros(["nro_pedido", "foto"], "POST", true))->add(new UserMiddleware(["Socio", "Mozo"]));
	$group->get('/pendientes', \PedidoController::class . ':ObtenerPedidosDeRol')->add(\UserMiddleware::class . ':VerificarToken');
	$group->post('/iniciar', \PedidoController::class . ':IniciarPreparacionProducto')->add(new VerificarParametros(["nro_pedido", "item_id", "prep_time"], "POST", false))->add(\UserMiddleware::class . ':VerificarToken');
	$group->get('/demora', \PedidoController::class . ':EsperaPedido')->add(new VerificarParametros(["nro_pedido"], "GET", false));
	$group->post('/listo', \PedidoController::class . ':ItemListo')->add(new VerificarParametros(["nro_pedido", "item_id"], "POST", false))->add(\UserMiddleware::class . ':VerificarToken');
	$group->get('/listo', \PedidoController::class . ':PedidosListos')->add(new UserMiddleware(["Socio", "Mozo"]));
	$group->post('/entregar', \PedidoController::class . ':EntregarPedido')->add(\MesaController::class . ':EntregarPedido')->add(new VerificarParametros(["codigo"], "POST", false))->add(new UserMiddleware(["Socio", "Mozo"]));
	$group->get('/cuenta', \PedidoController::class . ':ObtenerCuenta')->add(new VerificarParametros(["nro_pedido"], "GET", false))->add(new UserMiddleware(["Socio", "Mozo"]));
});

$app->group('/csv', function (RouteCollectorProxy $group) {
	$group->post('[/]', \ProductoController::class . ':GuardarProductos');
	$group->put('[/]', \ProductoController::class . ':CargarProductos');
});

$app->group('/encuesta', function (RouteCollectorProxy $group) {
	$group->post('[/]', \EncuestaController::class . ':CargarUna')->add(new VerificarParametros(["puntuacion_mesa", "puntuacion_mozo", "puntuacion_restaurante", "puntuacion_cocinero", "nro_mesa", "nro_pedido", "comentario"], "POST", false));
	$group->get('[/]', \EncuestaController::class . ':MejorComentario')->add(new UserMiddleware(["Socio"]));
});

$app->run();