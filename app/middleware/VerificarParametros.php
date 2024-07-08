<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class VerificarParametros
{

    public array $parametrosAEvaluar = array();
    public string $metodo;

    public bool $incluyeFoto;

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $parametros = array();
        switch ($this->metodo) {
            case "PUT":
            case "POST":
                $parametros = $request->getParsedBody();
                break;
            case "GET":
                $parametros = $_GET;
                break;
        }
        if ($this->incluyeFoto) {
            $parametros['foto'] = $_FILES['foto'];
        }
        $correcto = true;
        foreach ($this->parametrosAEvaluar as $parametro) {
            switch ($parametro) {
                case "tipo":
                    if (!array_key_exists($parametro, $parametros) || !($parametros["tipo"] == "Camiseta" || $parametros["tipo"] == "Pantalon")) {
                        $correcto = false;
                    }
                    break;
                case "talla":
                    if (!array_key_exists($parametro, $parametros) || !($parametros["talla"] == "S" || $parametros["talla"] == "M" || $parametros["talla"] == "L")) {
                        $correcto = false;
                    }
                    break;
                case "perfil":
                    if (!array_key_exists($parametro, $parametros) || !($parametros["perfil"] == "Admin" || $parametros["perfil"] == "Empleado")) {
                        $correcto = false;
                    }
                    break;
                default:
                    if (!array_key_exists($parametro, $parametros)) {
                        $correcto = false;
                    }
                    break;
            }
            if (!$correcto) {
                break;
            }
        }

        if ($correcto) {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => "Hubo un error en los parametros"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function __construct(array $parametros, string $metodo, bool $incluyeFoto)
    {
        $this->parametrosAEvaluar = $parametros;
        $this->metodo = $metodo;
        $this->incluyeFoto = $incluyeFoto;
    }
}