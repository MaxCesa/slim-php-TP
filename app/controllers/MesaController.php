<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController
{
  public function CargarUno($request, $response, $args)
  {
    $mesa = new Mesa(substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5), "Con cliente esperando pedido");
    $mesa->crearMesa();
    $payload = json_encode(array("mensaje" => "Mesa creada con exito, su numero es: " . $mesa->code));
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


}