<?php

require_once './models/Encuesta.php';
require_once './interfaces/IApiUsable.php';

class EncuestaController extends Encuesta
{
  public static function CargarUna($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $encuesta = new Encuesta();
    if (
      isset($parametros['puntuacion_mesa']) && $parametros['puntuacion_mesa'] != "" &&
      isset($parametros['puntuacion_mozo']) && $parametros['puntuacion_mozo'] != "" &&
      isset($parametros['puntuacion_restaurante']) && $parametros['puntuacion_restaurante'] != "" &&
      isset($parametros['puntuacion_cocinero']) && $parametros['puntuacion_cocinero'] != "" &&
      isset($parametros['nro_mesa']) && $parametros['nro_mesa'] != "" &&
      isset($parametros['nro_pedido']) && $parametros['nro_pedido'] != "" &&
      isset($parametros['comentario']) && $parametros['comentario'] != ""
    ) {
      $encuesta->puntuacion_mesa = $parametros['puntuacion_mesa'];
      $encuesta->puntuacion_mozo = $parametros['puntuacion_mozo'];
      $encuesta->puntuacion_restaurante = $parametros['puntuacion_restaurante'];
      $encuesta->puntuacion_cocinero = $parametros['puntuacion_cocinero'];
      $encuesta->comentario = $parametros['comentario'];
      $encuesta->mesa_id = Mesa::obtenerIdSegunCodigo($parametros['nro_mesa'])['id'];
      $encuesta->pedido_id = Pedido::obtenerIdSegunCodigo($parametros['nro_pedido'])['id'];
      $encuesta->crearEncuesta();
      $payload = json_encode(array("mensaje" => "Encuesta creada con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    } else {
      $payload = json_encode(array("mensaje" => "ERROR"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }


  public static function MejorComentario($request, $response, $args)
  {
    $rta = Encuesta::obtenerMejorComentario();
    $payload = json_encode(array("mensaje" => $rta));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}

?>