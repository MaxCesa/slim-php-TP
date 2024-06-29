<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;



class UserMiddleware
{


    public static function ValidarSocio(Request $request, RequestHandler $handler): Response
    {

        if (isset($_SESSION['jwt_token'])) {
            $payload = Token::DecodificarToken($_SESSION['jwt_token'])['Payload'];

            if ($payload->tipo == "Socio") {
                $res = $handler->handle($request);
                return $res;
            } else {
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401, 'No autorizado');
                $response->getBody()->write("Solo para socios...");
                return $response;
            }
        } else {
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $responseFactory->createResponse(401, 'No autorizado');
            $response->getBody()->write("No estas logeado.");
            return $response;
        }


    }

    public static function ValidarMozo(Request $request, RequestHandler $handler): Response
    {
        if (isset($_SESSION['jwt_token'])) {
            $payload = Token::DecodificarToken($_SESSION['jwt_token'])['Payload'];

            if ($payload->tipo == "Socio" || $payload->tipo == "Mozo") {
                $res = $handler->handle($request);
                return $res;
            } else {
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401, 'No autorizado');
                $response->getBody()->write("Solo para socios...");
                return $response;
            }
        } else {
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $responseFactory->createResponse(401, 'No autorizado');
            $response->getBody()->write("No estas logeado.");
            return $response;
        }
    }

    public static function ValidarCervecero(Request $request, RequestHandler $handler): Response
    {
        if (isset($_SESSION['jwt_token'])) {
            $payload = Token::DecodificarToken($_SESSION['jwt_token'])['Payload'];

            if ($payload->tipo == "Socio" || $payload->tipo == "Cervecero") {
                $res = $handler->handle($request);
                return $res;
            } else {
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401, 'No autorizado');
                $response->getBody()->write("Solo para socios...");
                return $response;
            }
        } else {
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $responseFactory->createResponse(401, 'No autorizado');
            $response->getBody()->write("No estas logeado.");
            return $response;
        }
    }

    public static function ValidarBartender(Request $request, RequestHandler $handler): Response
    {
        if (isset($_SESSION['jwt_token'])) {
            $payload = Token::DecodificarToken($_SESSION['jwt_token'])['Payload'];

            if ($payload->tipo == "Socio" || $payload->tipo == "Bartender") {
                $res = $handler->handle($request);
                return $res;
            } else {
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401, 'No autorizado');
                $response->getBody()->write("Solo para socios...");
                return $response;
            }
        } else {
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $responseFactory->createResponse(401, 'No autorizado');
            $response->getBody()->write("No estas logeado.");
            return $response;
        }


    }

    public static function ValidarCocinero(Request $request, RequestHandler $handler): Response
    {
        if (isset($_SESSION['jwt_token'])) {
            $payload = Token::DecodificarToken($_SESSION['jwt_token'])['Payload'];

            if ($payload->tipo == "Socio" || $payload->tipo == "Cocinero") {
                $res = $handler->handle($request);
                return $res;
            } else {
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
                $response = $responseFactory->createResponse(401, 'No autorizado');
                $response->getBody()->write("Solo para socios...");
                return $response;
            }
        } else {
            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $response = $responseFactory->createResponse(401, 'No autorizado');
            $response->getBody()->write("No estas logeado.");
            return $response;
        }
    }

}
