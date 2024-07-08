<?php

require_once "PDF.php";

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $tipo;


    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre,precio,tipo) VALUES (:nombre,:precio,:tipo)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_INT);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, precio, tipo FROM productos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function ObtenerSector($id)
    {
        $pdo = AccesoDatos::obtenerInstancia();
        $consulta = $pdo->prepararConsulta("SELECT tipo FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function GuardarProductosCSV()
    {
        $datos = Producto::obtenerTodos();

        $file = fopen("./csv/productos.csv", 'w');

        $propiedades = array_keys(get_object_vars($datos[0]));
        fputcsv($file, $propiedades);

        foreach ($datos as $producto) {
            $row = [];
            foreach ($propiedades as $propiedad) {
                $row[] = $producto->$propiedad;
            }
            fputcsv($file, $row);
        }
        return $datos;
    }

    public static function actualizarProducto(Producto $producto)
    {
        $pdo = AccesoDatos::obtenerInstancia();
        $consulta = $pdo->prepararConsulta("REPLACE INTO productos (id,nombre,precio,tipo)
                                            VALUES (:id,:nombre,:precio,:tipo);");
        $consulta->bindValue(":nombre", $producto->nombre);
        $consulta->bindValue(":precio", $producto->precio);
        $consulta->bindValue(":tipo", $producto->tipo);
        $consulta->bindValue(":id", $producto->id);
        $consulta->execute();
    }

    public static function actualizarSQLconCSV()
    {
        $productos = [];

        try {
            // Open the CSV file for reading
            $file = fopen(".\csv\productos.csv", 'r');
            if ($file === false) {
                throw new Exception("No se pudo abrir el archivo: .\csv\productos.csv");
            }

            // Read the column headers
            $propiedades = fgetcsv($file);
            if ($propiedades === false) {
                throw new Exception("No se pudo leer la primera fila.");
            }

            while (($fila = fgetcsv($file)) !== false) {
                $datos = array_combine($propiedades, $fila);

                $producto = new Producto();
                $producto->id = $datos['id'];
                $producto->nombre = $datos['nombre'];
                $producto->precio = $datos['precio'];
                $producto->tipo = $datos['tipo'];


                $productos[] = $producto;
            }

            foreach ($productos as $producto) {
                Producto::actualizarProducto($producto);
            }

            fclose($file);

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        return $productos;
    }

    public static function verificarCompatibilidad($rol, $id_producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT tipo FROM productos WHERE id = :id");
        $consulta->bindValue(":id", $id_producto, PDO::PARAM_INT);
        $consulta->execute();

        $retorno = false;
        switch (($consulta->fetch(PDO::FETCH_ASSOC))['tipo']) {
            case "Comida":
                if ($rol == "Cocinero") {
                    $retorno = true;
                }
                break;
            case "Cerveza":
                if ($rol == "Cervecero") {
                    $retorno = true;
                }
                break;
            case "Trago":
                if ($rol == "Bartender") {
                    $retorno = true;
                }
                break;
        }
        return $retorno;
    }

    public static function ProductosAPDF()
    {
        $productos = Producto::obtenerTodos();
        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->ProductosTable(array_keys(get_object_vars($productos[0])), $productos);
        $pdf->Output();
    }

}
