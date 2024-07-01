<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

class MesaController
{
  public function CargarUno($request, $response, $args)
  {
    $mesa = new Mesa(substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5), "Con cliente esperando pedido");
    $mesa->crearMesa();
    $payload = json_encode(array("mensaje" => "Mesa creada con exito, su numero es: " . $mesa->codigo));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Mesa::obtenerTodos();
    $payload = json_encode(array("listaMesas" => $lista));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function EntregarPedido($request, RequestHandler $handler)
  {
    $factory = new ResponseFactory();
    $response = $factory->createResponse();
    $parametros = $request->getParsedBody();
    if (isset($parametros['codigo'])) {
      $resultado = Mesa::cambiarEstadoMesa($parametros['codigo'], "Con cliente comiendo");
      if ($resultado > 0) {
        $response = $handler->handle($request);
        return $response
          ->withHeader('Content-Type', 'application/json');
      } else {
        $payload = json_encode(array("Mensaje" => "No se pudo entregar la comida."));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      }
    } else {
      $payload = json_encode(array("Mensaje" => "Parametros incorrectos"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }
  public function CerrarMesa($request, $response, $args)
  {
    $factory = new ResponseFactory();
    $response = $factory->createResponse();
    $parametros = $request->getParsedBody();
    if (isset($parametros['codigo'])) {
      $resultado = Mesa::cambiarEstadoMesa($parametros['codigo'], "Cerrada");
      if ($resultado > 0) {
        $payload = json_encode(array("Mensaje" => "Mesa Cerrada."));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      } else {
        $payload = json_encode(array("Mensaje" => "No se pudo cerrar la mesa."));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      }
    } else {
      $payload = json_encode(array("Mensaje" => "Parametros incorrectos"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public function MesaMasUsada($request, $response, $args)
  {
    $mesa = Mesa::GetMesaMasUsada();
    $payload = json_encode(array("Mensaje" => "El codigo de la mesa mas usada es {$mesa}."));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}