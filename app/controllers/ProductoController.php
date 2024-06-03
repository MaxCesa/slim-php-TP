<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    if (
      isset($parametros['nombre']) && $parametros['nombre'] != "" &&
      isset($parametros['precio']) && $parametros['precio'] != "" &&
      isset($parametros['tiempo_preparacion']) && $parametros['tiempo_preparacion'] != "" &&
      ($parametros['tipo'] == "Comida" || $parametros['tipo'] == "Bebida")
    ) {
      $producto = new Producto();
      $producto->nombre = $parametros['nombre'];
      $producto->precio = $parametros['precio'];
      $producto->tipo = $parametros['tipo'];
      $producto->tiempo_preparacion = $parametros['tiempo_preparacion'];
      $producto->crearProducto();
      $payload = json_encode(array("mensaje" => "Producto creado con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Ocurrio un error al crear el producto"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Producto::obtenerTodos();
    $payload = json_encode(array("listaProductos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}