<?php

class Mesa
{
    public $id;
    public $codigo;
    public $estado;

    public function __construct($codigo, $state, $id = "")
    {
        $this->codigo = $codigo;
        $this->estado = $state;
        $this->id = $id;
    }
    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado) VALUES (:codigo, :estado)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, estado FROM mesas");
        $consulta->execute();
        $retorno = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $mesas = array();
        foreach ($retorno as $mesa) {
            $mesas[] = new Mesa($mesa['codigo'], $mesa['estado'], $mesa['id']);
        }
        return $mesas;
    }



    public static function getByID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE id = :id");
        $consulta->bindParam(':id', $id, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }



    public static function obtenerIdSegunCodigo($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM mesas WHERE codigo = :codigo");
        $consulta->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function cambiarEstadoMesa($nro_mesa, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Mesa::obtenerIdSegunCodigo($nro_mesa);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado  WHERE id = :id AND estado != :estado1");
        $consulta->bindValue(':id', $id['id'], PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':estado1', $estado, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->rowCount();
    }


}