<?php


require_once 'vendor/autoload.php';


$app = new \Slim\Slim();


$app->get('/', function ($request, $response, array $args) {
		$response->getBody()->write("Funciona!");
return $response;
});


$app->run();
?>