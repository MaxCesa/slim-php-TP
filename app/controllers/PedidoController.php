<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    if (
      isset($parametros['producto']) && $parametros['producto'] != "" &&
      isset($parametros['cliente']) && $parametros['cliente'] != "" &&
      isset($parametros['mesa']) && $parametros['mesa'] != "" &&
      isset($parametros['cantidad']) && $parametros['cantidad'] != ""
    ) {
      $pedido = new Pedido();
      $pedido->cliente = $parametros['cliente'];
      $mesa_id = Mesa::obtenerIdSegunCodigo($parametros['mesa']);
      var_dump($mesa_id);
      $pedido->mesa_id = $mesa_id['id'];
      $pedido->codigo = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
      if (isset($_FILES['foto'])) {
        $pedido->foto = $_FILES['foto']['name'];
        Pedido::SubirFotoPedido($_FILES['foto'], $pedido->codigo);
      }
      $pedido_id = $pedido->crearPedido();
      foreach ($parametros['producto'] as $key => $producto) {
        $item = Pedido::crearItemPedido($pedido_id, $producto, $parametros['tiempo_estimado'][$key]);
      }
      Mesa::cambiarEstadoMesa($parametros['mesa'], 1);
      $payload = json_encode(array("mensaje" => "Pedido creado con exito, Su código de pedido es: " . $pedido->codigo));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Ocurrio un error al crear el pedido"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::obtenerTodos();
    $payload = json_encode(array("listaPedidos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function SubirFoto($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    if (isset($parametros['nro_pedido']) && $parametros['nro_pedido'] != "" && isset($_FILES['foto'])) {
      Pedido::SubirFotoPedido($_FILES['foto'], $parametros['nro_pedido']);
      $rta = Pedido::UpdateFoto($parametros['nro_pedido'], $_FILES['foto']['name']);
      if ($rta > 0) {
        $payload = json_encode(array("mensaje" => "Foto subida"));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      } else {
        $payload = json_encode(array("mensaje" => "ERROR"));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      }
    } else {
      $payload = json_encode(array("mensaje" => "ERROR AL CARGAR LOS CAMPOS"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }















}