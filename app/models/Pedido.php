<?php

require_once 'Token.php';
require_once "Producto.php";
require_once "Mesa.php";
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

    public function crearItemPedido($pedido, $producto, $id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos_pedidos (id_pedido,id_producto) VALUES (:id_pedido,:producto_id)");
        $consulta->bindValue(':id_pedido', $pedido, PDO::PARAM_INT);
        $consulta->bindValue(':producto_id', $producto, PDO::PARAM_INT);
        //$consulta->bindValue(':estado', 0, PDO::PARAM_INT);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }


    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.*, pp.demora FROM pedidos p
                                                        LEFT JOIN 
                                                        ( SELECT id_pedido, MAX(demora) AS demora FROM productos_pedidos
                                                        GROUP BY id_pedido ) 
                                                        pp ON p.id = pp.id_pedido;");
        $consulta->execute();
        $pedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        return $pedidos;
    }

    public static function obtenerPedidosDeTipo($rol, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(" SELECT q.id as id_producto, q.nombre as producto, i.id_pedido as pedido FROM productos_pedidos as i
        LEFT JOIN productos as q ON q.id = i.id_producto
        WHERE q.tipo = :tipo AND i.estado = :estado;");
        switch ($rol) {
            case "Cocinero":
                $consulta->bindValue(':tipo', "Comida");
                break;
            case "Bartender":
                $consulta->bindValue(':tipo', "Trago");
                break;
            case "Cervecero":
                $consulta->bindValue(':tipo', "Cerveza");
                break;
            default:
                $consulta->bindValue(':tipo', 1, PDO::PARAM_INT);
        }

        $consulta->bindValue(':estado', $estado);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function IniciarPreparacion($nro_pedido, $item_id, $prep_time, $rol_usuario, $id_usuario)
    {
        if (Producto::verificarCompatibilidad($rol_usuario, $item_id)) {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos_pedidos as i
            SET estado = 'En preparacion', tiempo_estimado = :tiempo_estimado, hora_de_inicio = NOW(), id_usuario = :id_usuario
            WHERE i.id_pedido = :id_pedido AND i.id_producto = :item_id AND estado = 'Sin asignar'");
            $consulta->bindValue(':tiempo_estimado', $prep_time, PDO::PARAM_INT);
            $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
            $consulta->bindValue(':item_id', $item_id, PDO::PARAM_INT);
            $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $consulta->execute();

            return $consulta->rowCount();
        } else {
            return 0;
        }

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

    public static function obtenerEsperaPedido($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("SELECT MAX(tiempo_estimado) as Espera FROM `productos_pedidos`
        WHERE `id_pedido` = :id_pedido ORDER BY tiempo_estimado DESC");
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch()['Espera'];
    }

    public static function obtenerDemoraPedido($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("SELECT MAX(demora) as Demora FROM `productos_pedidos`
        WHERE `id_pedido` = :id_pedido ORDER BY demora DESC");
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch()['Demora'];
    }

    public static function ObtenerPedidosYDemoras()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT o.id_pedido AS pedido, MAX(o.tiempo_estimado) AS demora FROM productos_pedidos as o
                                                        GROUP BY o.id_pedido;");
        $consulta->execute();
        return $consulta->fetchAll();
    }

    public static function SetItemListo($nro_pedido, $id_producto, $id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos_pedidos
                                                        SET
                                                        estado = 'Listo',
                                                        hora_finalizacion = NOW(),
                                                        demora = GREATEST(TIMESTAMPDIFF(MINUTE,hora_de_inicio, NOW()) - tiempo_estimado, 0)
                                                        WHERE id_pedido = :id_pedido AND id_usuario = :id_usuario AND id_producto = :id_producto
                                                        ORDER BY hora_de_inicio DESC;");
        $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->execute();

        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) = SUM(estado = 'Listo') AS ready
                                                        FROM  productos_pedidos
                                                        WHERE id_pedido = :id_pedido;");
        $consulta->bindValue(":id_pedido", $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        if ($consulta->fetch()['ready'] == 1) {
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos
                                                            SET estado = 'Listo'
                                                            WHERE id = :id_pedido;");
            $consulta->bindValue(":id_pedido", $id['id'], PDO::PARAM_INT);
            $consulta->execute();
        }
        return $consulta->rowCount();
    }

    public static function SetItemEnPreparacion($nro_pedido, $id_producto, $id_usuario, $rol_usuario)
    {
        if (Producto::verificarCompatibilidad($rol_usuario, $id_producto)) {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos_pedidos
                                                            SET estado = 'En preparacion',
                                                            hora_de_inicio = NOW()
                                                            WHERE id_pedido = :id_pedido AND id_usuario = :id_usuario AND id_producto = :id_producto;");
            $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
            $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
            $consulta->execute();
            if ($consulta->rowCount() > 0) {
                $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos_pedidos
                                                                SET estado = 'En preparacion'
                                                                WHERE id_pedido = :id_pedido;");
                $consulta->bindValue(':id_pedido', $id['id'], PDO::PARAM_INT);
                $consulta->execute();
            }
            return $consulta->rowCount();
        }
    }

    public static function obtenerPedidosListos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos
                                                        WHERE estado = 'Listo';");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function SetPedidoEntregado($codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Mesa::obtenerIdSegunCodigo($codigo_mesa);
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos
                                                        SET estado = 'En mesa'
                                                        WHERE mesa_id = :codigo_mesa;");
        $consulta->bindValue(':codigo_mesa', $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function CalcularCuenta($nro_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $id = Pedido::obtenerIdSegunCodigo($nro_pedido);
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(precio) as total FROM productos as p
                                                        JOIN productos_pedidos as pp ON p.id = pp.id_producto
                                                        WHERE pp.id_pedido = :id_pedido;");
        $consulta->bindValue(":id_pedido", $id['id'], PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC)['total'];
    }

}
