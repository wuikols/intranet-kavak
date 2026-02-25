<?php
class Location {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las regiones
    public function getRegiones() {
        try {
            $query = "SELECT * FROM regiones ORDER BY id ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    // Obtener provincias filtradas por región
    public function getProvinciasByRegion($region_id) {
        if (!$region_id) return [];
        try {
            $query = "SELECT * FROM provincias WHERE region_id = ? ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$region_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    // Obtener comunas filtradas por provincia
    public function getComunasByProvincia($provincia_id) {
        if (!$provincia_id) return [];
        try {
            $query = "SELECT * FROM comunas WHERE provincia_id = ? ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$provincia_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    // CORRECCIÓN MAESTRA: Obtener comunas por región usando JOIN
    // Esto soluciona el error "Column not found: region_id"
    public function getComunasByRegion($region_id) {
        if (!$region_id) return [];
        try {
            // Unimos comunas con provincias para filtrar por la región de la provincia
            $query = "SELECT c.* FROM comunas c 
                      INNER JOIN provincias p ON c.provincia_id = p.id 
                      WHERE p.region_id = ? 
                      ORDER BY c.nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$region_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Si falla la estructura normalizada, retornamos array vacío para no romper la web
            return [];
        }
    }

    // Obtener todas las comunas (para listados generales)
    public function getComunas() {
        try {
            $query = "SELECT * FROM comunas ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>