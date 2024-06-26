<?php

require_once "./db/AccesoDatos.php";
class Usuario
{
    public $id;
    public $usuario;
    public $clave;
    public $tipo;

    public function __construct(string $usuario, string $clave, $id = "")
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->clave = $clave;
    }

    public function ingresarUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, clave, tipo) VALUES (:usuario, :clave, :tipo)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':tipo', $this->tipo);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos($tipo = "")
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        if ($tipo == "") {
            $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, tipo FROM usuarios");
        } else {
            $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave FROM usuarios WHERE tipo = :tipo");
            $consulta->bindValue("tipo", $tipo);
        }

        $consulta->execute();
        $lista = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $usr = array();
        foreach ($lista as $usuario) {
            switch ($usuario['tipo']) {
                case "Mozo":
                    $usr[] = new Mozo($usuario['usuario'], $usuario['clave'], $usuario['id']);
                    break;
                case "Socio":
                    $usr[] = new Socio($usuario['usuario'], $usuario['clave'], $usuario['id']);
                    break;
                case "Bartender":
                    $usr[] = new Bartender($usuario['usuario'], $usuario['clave'], $usuario['id']);
                    break;
                case "Cervecero":
                    $usr[] = new Cervecero($usuario['usuario'], $usuario['clave'], $usuario['id']);
                    break;
                case "Cocinero":
                    $usr[] = new Cocinero($usuario['usuario'], $usuario['clave'], $usuario['id']);
                    break;
                default:
                    break;
            }
        }

        return $usr;

    }

    public static function obtenerUsuarioPorNombre($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
        $retorno = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($retorno) {
            switch ($retorno['tipo']) {
                case "Mozo":
                    return new Mozo($retorno['usuario'], $retorno['clave']);
                case "Socio":
                    return new Socio($retorno['usuario'], $retorno['clave']);
                case "Bartender":
                    return new Bartender($retorno['usuario'], $retorno['clave']);
                case "Cervecero":
                    return new Cervecero($retorno['usuario'], $retorno['clave']);
                case "Cocinero":
                    return new Cocinero($retorno['usuario'], $retorno['clave']);
            }
        }
    }

    public function modificarUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET usuario = :usuario, clave = :clave WHERE id = :id");
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    public static function ObtenerMasLibre($tipo)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        switch ($tipo) {
            case "Comida":
                $consulta = $objAccesoDato->prepararConsulta(
                    "SELECT u.id, COUNT(o.estado) AS empleado_libre
                    FROM usuarios u
                    LEFT JOIN orders o ON u.id = o.id_usuario AND o.estado = 'En preparacion'
                    WHERE u.tipo = 'Cocinero'
                    GROUP BY u.id
                    ORDER BY empleado_libre ASC
                    LIMIT 1;"
                );
                break;
            case "Cerveza":
                $consulta = $objAccesoDato->prepararConsulta(
                    "SELECT u.id, COUNT(o.estado) AS empleado_libre
                    FROM usuarios u
                    LEFT JOIN orders o ON u.id = o.id_usuario AND o.estado = 'En preparacion'
                    WHERE u.tipo = 'Cervecero'
                    GROUP BY u.id
                    ORDER BY empleado_libre ASC
                    LIMIT 1;"
                );
                break;
            case "Trago":
                $consulta = $objAccesoDato->prepararConsulta(
                    "SELECT u.id, COUNT(o.estado) AS empleado_libre
                    FROM usuarios u
                    LEFT JOIN orders o ON u.id = o.id_usuario AND o.estado = 'En preparacion'
                    WHERE u.tipo = 'Bartender'
                    GROUP BY u.id
                    ORDER BY empleado_libre ASC
                    LIMIT 1;"
                );
                break;
            default:
                $consulta = $objAccesoDato->prepararConsulta("SELECT * FROM usuario WHERE FALSE");
                break;
        }
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);

    }
}

class Mozo extends Usuario
{
    public function __construct(string $usuario, string $clave, $id = "")
    {
        parent::__construct($usuario, $clave, $id);
        $this->tipo = "Mozo";
    }

    public static function obtenerMozos()
    {
        return parent::obtenerTodos("Mozo");
    }
}
class Socio extends Usuario
{
    public function __construct(string $usuario, string $clave, $id = "")
    {
        parent::__construct($usuario, $clave, $id);
        $this->tipo = "Socio";
    }

    public static function obtenerSocios()
    {
        return parent::obtenerTodos("Socio");
    }
}
class Bartender extends Usuario
{
    public function __construct(string $usuario, string $clave, $id = "")
    {
        parent::__construct($usuario, $clave, $id);
        $this->tipo = "Bartender";
    }

    public static function obtenerBartenders()
    {
        return parent::obtenerTodos("Bartender");
    }
}
class Cocinero extends Usuario
{
    public function __construct(string $usuario, string $clave, $id = "")
    {
        parent::__construct($usuario, $clave, $id);
        $this->tipo = "Cocinero";
    }

    public static function obtenerCocineros()
    {
        return parent::obtenerTodos("Cocinero");
    }
}
class Cervecero extends Usuario
{
    public function __construct(string $usuario, string $clave, $id = "")
    {
        parent::__construct($usuario, $clave, $id);
        $this->tipo = "Cervecero";
    }

    public static function obtenerCerveceros()
    {
        return parent::obtenerTodos("Cervecero");
    }
}
