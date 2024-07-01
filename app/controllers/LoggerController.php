<?php
require_once './models/Logger.php';
require_once './models/Token.php';

class LoggerController extends Logger
{
  public function LogIn($request, $response, $args)
  {

    $parametros = $request->getParsedBody();

    if (
      isset($parametros['username']) && $parametros['username'] != "" &&
      isset($parametros['pw']) && $parametros['pw'] != ""
    ) {
      $log = new Logger();
      $log->username = $parametros['username'];
      $log->pw = $parametros['pw'];
      $resultado = $log->log_in();
      if ($resultado) {
        $token = Token::CodificarToken($resultado['usuario'], $resultado['tipo'], $resultado['id']);
        $payload = json_encode(array("mensaje" => "OK token: " . $token));
        $_SESSION['jwt_token'] = $token;
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      } else {
        $payload = json_encode(array("mensaje" => "Usuario o contraseña incorrectos"));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
      }

    } else {
      $payload = json_encode(array("mensaje" => "Ocurrio un error"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
  }

  public static function LogOut($request, $response, $args)
  {
    $_SESSION[] = array();
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }
    session_destroy();
    $payload = json_encode(array("mensaje" => "Deslogueado"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}