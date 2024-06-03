<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
class UsuarioController implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    if (
      isset($parametros['tipo']) && $parametros['tipo'] != "" &&
      isset($parametros['usuario']) && $parametros['usuario'] != "" &&
      isset($parametros['clave']) && $parametros['clave'] != ""
    ) {
      switch ($parametros['tipo']) {
        case "Mozo":
          $usr = new Mozo($parametros['usuario'], $parametros['clave']);
          break;
        case "Socio":
          $usr = new Socio($parametros['usuario'], $parametros['clave']);
          break;
        case "Bartender":
          $usr = new Bartender($parametros['usuario'], $parametros['clave']);
          break;
        case "Cervecero":
          $usr = new Cervecero($parametros['usuario'], $parametros['clave']);
          break;
        case "Cocinero":
          $usr = new Cocinero($parametros['usuario'], $parametros['clave']);
          break;
        default:
          break;
      }
      $usr->ingresarUsuario();
      $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    } else {
      $payload = json_encode(array("mensaje" => "Ocurrio un error"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Usuario::obtenerTodos();

    $payload = json_encode(array("listaUsuario" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}