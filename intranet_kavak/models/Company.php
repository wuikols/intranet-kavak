<?php
class Company {
    private $conn;

    public function __construct($db) { $this->conn = $db; }

    // --- SUCURSALES (HUBS) ---
    public function getSucursales() {
        $stmt = $this->conn->prepare("SELECT * FROM sucursales ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSucursalById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM sucursales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSucursal($nombre, $corto, $direccion, $comuna_id) {
        $sql = "INSERT INTO sucursales (nombre, nombre_corto, direccion, comuna_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $corto, $direccion, $comuna_id]);
    }

    public function updateSucursal($id, $nombre, $corto, $direccion, $comuna_id) {
        $sql = "UPDATE sucursales SET nombre=?, nombre_corto=?, direccion=?, comuna_id=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $corto, $direccion, $comuna_id, $id]);
    }
    
    // NUEVO: Actualizar Horario Operativo del HUB
    public function updateHorarioHub($id, $apertura, $cierre) {
        $sql = "UPDATE sucursales SET hora_apertura=?, hora_cierre=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$apertura, $cierre, $id]);
    }

    public function deleteSucursal($id) {
        $this->conn->prepare("DELETE FROM usuarios WHERE sucursal_id = ?")->execute([$id]); // Desvincular usuarios
        $stmt = $this->conn->prepare("DELETE FROM sucursales WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- CARGOS ---
    public function getCargos() {
        $stmt = $this->conn->prepare("SELECT * FROM cargos ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createCargo($nombre) {
        $stmt = $this->conn->prepare("INSERT INTO cargos (nombre) VALUES (?)");
        return $stmt->execute([$nombre]);
    }

    public function updateCargo($id, $nombre) {
        $stmt = $this->conn->prepare("UPDATE cargos SET nombre=? WHERE id=?");
        return $stmt->execute([$nombre, $id]);
    }

    public function deleteCargo($id) {
        $stmt = $this->conn->prepare("DELETE FROM cargos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- NUEVO: GESTIÓN DE TIPOS DE TURNOS (CONFIGURACIÓN) ---
    
    public function getTurnosConfig($sucursal_id) {
        $stmt = $this->conn->prepare("SELECT * FROM config_turnos WHERE sucursal_id = ? ORDER BY hora_inicio ASC");
        $stmt->execute([$sucursal_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTurnoConfig($sucursal_id, $nombre, $inicio, $fin, $color) {
        $sql = "INSERT INTO config_turnos (sucursal_id, nombre, hora_inicio, hora_fin, color) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$sucursal_id, $nombre, $inicio, $fin, $color]);
    }

    public function deleteTurnoConfig($id) {
        $stmt = $this->conn->prepare("DELETE FROM config_turnos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>