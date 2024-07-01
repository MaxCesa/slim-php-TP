<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;



class UserMiddleware
{


    public static function ValidarSocio(Request $request, RequestHandler $handler): Response
    {

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Payload']->tipo == "Socio") {
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

    public static function ValidarMozo(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $response = new Response();
        //TODO: No hay excepcion para verificacion
        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Payload']->tipo == "Mozo" || $decoded['Payload']->tipo == "Socio") {
                $response = $handler->handle($request);
            } else {
                throw new Exception("Error en la decodificacion de TOKEN");
            }
        } catch (Exception $e) {

            $payload = json_encode(array('mensaje' => $e->getMessage()));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarCervecero(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Payload']->tipo == "Cervecero" || $decoded['Payload']->tipo == "Socio") {
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

    public static function ValidarBartender(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Payload']->tipo == "Bartender" || $decoded['Payload']->tipo == "Socio") {
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

    public static function ValidarCocinero(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $decoded = Token::DecodificarToken($token);
            if ($decoded['Payload']->tipo == "Cocinero" || $decoded['Payload']->tipo == "Socio") {
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
