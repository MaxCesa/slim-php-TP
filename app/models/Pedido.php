<?php

class Pedido
{
    public $id;
    public $estado;
    public $cliente;
    public $foto = null;
    public $demora = null;
    public $mesa_id;
    public $codigo;

    private static function ObtenerPedidoState($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT MIN(estado) AS 'estado'  FROM productos_pedidos
        WHERE id_pedido = :id;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);

    }

    public static function SubirFotoPedido($archivo, $pedido)
    {
        $dir_subida = './Fotos/';
        if (!file_exists($dir_subida)) {
            mkdir($dir_subida, 0777, true);
            echo 'Se creó el directorio';
        }
        $fecha = date('Y-m-d');
        if (move_uploaded_file($archivo['tmp_name'], $dir_subida . $pedido . '.jpg')) {
            echo "Se creó correctamente el archivo";
        } else {
            echo "¡Error!\n";
        }
    }


    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (cliente,foto,mesa_id,codigo) VALUES (:cliente,:foto,:mesa_id,:codigo)");
        $consulta->bindValue(':cliente', $this->cliente, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':mesa_id', $this->mesa_id, PDO::PARAM_STR);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public function crearItemPedido($pedido, $producto, $tiempo_estimado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos_pedidos (id_pedido,id_producto,id_usuario,tiempo_estimado) VALUES (:id_pedido,:producto_id,:id_usuario,:tiempo_estimado)");
        $consulta->bindValue(':id_pedido', $pedido, PDO::PARAM_INT);
        $consulta->bindValue(':producto_id', $producto, PDO::PARAM_INT);
        $consulta->bindValue(':id_usuario', Usuario::ObtenerMasLibre(Producto::ObtenerSector($producto))['empleado_libre'], PDO::PARAM_INT);
        //$consulta->bindValue(':estado', 0, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $tiempo_estimado, PDO::PARAM_INT);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }


    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,cliente,codigo,mesa_id FROM pedidos");
        $consulta->execute();
        $pedidos = $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
        foreach ($pedidos as $key => $value) {
            $value->estado = Pedido::ObtenerPedidoState($value->id)['estado'];
        }
        return $pedidos;
    }

    public static function obtenerPedidos($id, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(" SELECT q.product_name as producto, i.id_pedido as pedido, i.cantidad as cantidad, i.estado as estado FROM productos_pedidos as i 
        LEFT JOIN pedidos as p ON i.id_pedido = p.id
        LEFT JOIN productos as q ON q.id = i.producto_id
        WHERE q.rol_id = :id AND i.estado = :estado;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function IniciarPreparacion($nro_pedido, $item_id, $prep_time)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = $this->obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos_pedidos as i
        SET estado = 1, prep_time = :prep_time
        WHERE i.id_pedido = :id_pedido AND i.producto_id = :item_id AND estado = 0");
        $consulta->bindValue(':prep_time', $prep_time, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->bindValue(':item_id', $item_id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function UpdateFoto($nro_pedido, $foto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = $this->obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos as p
        SET foto = :foto
        WHERE p.id = :id_pedido");
        $consulta->bindValue(':foto', $foto, PDO::PARAM_STR);
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }




    public static function obtenerIdSegunCodigo($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM pedidos WHERE codigo = :codigo");
        $consulta->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function ObtenerIdSegunMesa($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM pedidos WHERE mesa_id = :id");
        $consulta->bindParam(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


    public static function SetPedidoListo($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE estado FROM `pedidos` SET estado = 'Listo para servir'
        WHERE `id_pedido` = :id_pedido");
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function GetEstadoPedido($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("SELECT estado FROM `pedidos`
        WHERE `codigo` = :id_pedido");
        $consulta->bindValue(":nro_pedido", $nro_pedido);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC)['estado'];
    }

    public static function GetEstadoProductosPedidos($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM `productos_pedidos`
        WHERE `id_pedido` = :id_pedido");
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function SetProductoPedidoListo($id_producto, $nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE estado FROM `productos_pedidos` SET estado = 'Listo'
        WHERE `id_pedido` = :id_pedido AND 'id_producto' = :id_producto AND 'estado' = 'En preparacion'");
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->bindValue('id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();
    }







}