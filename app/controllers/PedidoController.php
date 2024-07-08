<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();


    $pedido = new Pedido();
    $pedido->cliente = $parametros['cliente'];
    $mesa_id = Mesa::obtenerIdSegunCodigo($parametros['mesa']);
    $pedido->mesa_id = $mesa_id['id'];
    $pedido->codigo = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
    if (isset($_FILES['foto'])) {
      $pedido->foto = $_FILES['foto']['name'];
      Pedido::SubirFotoPedido($_FILES['foto'], $pedido->codigo);
    }
    $pedido_id = $pedido->crearPedido();
    foreach ($parametros['producto'] as $key => $producto) {
      $item = Pedido::crearItemPedido($pedido_id, $producto, Token::IdActual($request));
    }
    Mesa::cambiarEstadoMesa($parametros['mesa'], 1);
    $payload = json_encode(array("mensaje" => "Pedido creado con exito, Su código de pedido es: " . $pedido->codigo));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');

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

  public static function ObtenerPedidosDeRol($request, $response, $args)
  {
    $pedidos = Pedido::obtenerPedidosDeTipo(Token::RolActual($request), "Sin asignar");
    $payload = json_encode(array("mensaje" => "Pedidos recibidos", "pedidos" => $pedidos));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function IniciarPreparacionProducto($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $retorno = Pedido::IniciarPreparacion($parametros['nro_pedido'], $parametros['item_id'], $parametros['prep_time'], Token::RolActual($request), Token::IdActual($request));
    if ($retorno > 0) {
      $payload = json_encode(array("mensaje" => "Estado de pedido actualizado"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Error iniciando pedido"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public static function EsperaPedido($request, $response, $args)
  {

    $demora = Pedido::obtenerEsperaPedido($_GET['nro_pedido']);
    if ($demora > 0) {
      $payload = json_encode(array("mensaje" => "Su pedido tardara {$demora} minutos"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Su pedido aun no ha sido procesado."));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public static function DemoraPedido($request, $response, $args)
  {


    $demora = Pedido::obtenerDemoraPedido($_GET['nro_pedido']);
    if ($demora > 0) {
      $payload = json_encode(array("mensaje" => "Su pedido esta {$demora} minutos tarde"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Su pedido sigue en proceso."));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

  }

  public static function ItemListo($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $retorno = Pedido::SetItemListo($parametros['nro_pedido'], $parametros['item_id'], Token::IdActual($request));
    if ($retorno > 0) {
      $payload = json_encode(array("mensaje" => "Estado de pedido actualizado"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public static function PedidosListos($request, $response, $args)
  {
    $pedidos = Pedido::obtenerPedidosListos();
    if (count($pedidos) > 0) {
      $payload = json_encode(array("Pedidos" => $pedidos));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "No hay pedidos listos"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public static function EntregarPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $resultado = Pedido::SetPedidoEntregado($parametros['codigo']);
    if ($resultado > 0) {
      $payload = json_encode(array("Mensaje" => "Producto entregado a la mesa"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Hubo un problema entregando el pedido"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public static function ObtenerCuenta($request, $response, $args)
  {

    $resultado = Pedido::CalcularCuenta($_GET['nro_pedido']);
    if ($resultado > 0) {
      $payload = json_encode(array("Mensaje" => "El total de la cuenta es de {$resultado}"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Hubo un problema calculando el total"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

  }

}