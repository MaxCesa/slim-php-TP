<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;



class UserMiddleware
{

    public array $tipo = array();

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Mensaje'] == "OK" && in_array($decoded['Payload']->tipo, $this->tipo)) {
                $response = $handler->handle($request);
            } else {
                throw new Exception("Error en la decodificacion de TOKEN");
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => $e->getMessage()));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function __construct($tipo)
    {
        $this->tipo = $tipo;
    }


    public static function verificarToken(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Mensaje'] == "OK") {
                $response = $handler->handle($request);
            } else {
                throw new Exception("Error en la decodificacion de TOKEN");
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => $e->getMessage()));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

}
