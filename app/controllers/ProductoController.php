<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();


    $producto = new Producto();
    $producto = $parametros['nombre'];
    $producto = $parametros['precio'];
    $producto = $parametros['tipo'];
    $producto->crearProducto();
    $payload = json_encode(array("mensaje" => "Producto creado con exito"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');

  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Producto::obtenerTodos();
    $payload = json_encode(array("listaProductos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function GuardarProductos($request, $response, $args)
  {
    $lista = Producto::GuardarProductosCSV();
    $payload = json_encode(array("listaProductos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CargarProductos($request, $response, $args)
  {
    $lista = Producto::actualizarSQLconCSV();
    $payload = json_encode(array("listaProductos" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function DescargarPDF($request, $response, $args)
  {
    Producto::ProductosAPDF();
  }

}