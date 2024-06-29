<?php


class Logger
{
    public string $username;
    public string $pw;
    public function log_in()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE usuario = :username AND clave = :pw");
        $consulta->bindValue(':username', $this->username, PDO::PARAM_STR);
        $consulta->bindValue(':pw', $this->pw, PDO::PARAM_STR);
        $consulta->execute();
        $resultado = $consulta->fetch();
        return $resultado;
    }


}