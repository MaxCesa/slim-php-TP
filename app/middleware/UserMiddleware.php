<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class UserMiddleware
{

    public static function ValidarSocio(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getQueryParams();

        $sector = $parametros['sector'];
        if ($sector === 'Socio') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No sos Socio'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarMozo(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getQueryParams();

        $sector = $parametros['sector'];
        if ($sector === 'Mozo') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No sos Mozo'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarCervecero(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getQueryParams();

        $sector = $parametros['sector'];
        if ($sector === 'Cervecero') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No sos Cervecero'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarBartender(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getQueryParams();

        $sector = $parametros['sector'];
        if ($sector === 'Bartender') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No sos Bartender'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarCocinero(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getQueryParams();

        $sector = $parametros['sector'];
        if ($sector === 'Cocinero') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'No sos Cocinero'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

}
?>