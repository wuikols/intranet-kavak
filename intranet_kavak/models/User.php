<?php
class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // =========================================================
    // 1. MÉTODOS DE AUTENTICACIÓN (LOGIN Y REGISTRO PÚBLICO)
    // =========================================================

    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, r.nombre as rol_nombre, r.p_noticias, r.p_usuarios, r.p_empresa, r.p_roles, r.p_dashboard_admin
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CORRECCIÓN APLICADA: Eliminada la columna 'created_at' que causaba el error
    public function createUser($email, $password, $nombre, $apellido)
    {
        $defaultRol = 2; // Rol 2 = Usuario estándar

        // Usamos NOW() para fecha_ingreso como "fecha de registro"
        $sql = "INSERT INTO usuarios (email, password, nombre, apellido, rol_id, fecha_ingreso) 
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $nombre,
            $apellido,
            $defaultRol
        ]);
    }

    // =========================================================
    // 2. MÉTODOS DE PERFIL Y LECTURA
    // =========================================================

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, 
                   c.nombre as cargo_nombre, 
                   s.nombre as sucursal_nombre, 
                   r.nombre as rol_nombre,
                   r.p_noticias, r.p_usuarios, r.p_empresa, r.p_roles, r.p_dashboard_admin
            FROM usuarios u
            LEFT JOIN cargos c ON u.cargo_id = c.id
            LEFT JOIN sucursales s ON u.sucursal_id = s.id
            LEFT JOIN roles r ON u.rol_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers()
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, 
                   c.nombre as cargo_nombre, 
                   s.nombre as sucursal_nombre,
                   r.nombre as rol_nombre,
                   r.p_noticias, r.p_usuarios, r.p_empresa, r.p_roles, r.p_dashboard_admin
            FROM usuarios u
            LEFT JOIN cargos c ON u.cargo_id = c.id
            LEFT JOIN sucursales s ON u.sucursal_id = s.id
            LEFT JOIN roles r ON u.rol_id = r.id
            ORDER BY u.nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data)
    {
        $sql = "UPDATE usuarios SET 
                nombre = :nombre, 
                apellido = :apellido, 
                apodo = :apodo, 
                telefono = :telefono,
                correo_personal = :correo_personal,
                linkedin = :linkedin,
                calle = :calle,
                numeracion = :numeracion,
                depto = :depto,
                region_id = :region_id,
                provincia_id = :provincia_id,
                comuna_id = :comuna_id,
                emergencia_nombre = :emergencia_nombre,
                emergencia_parentesco = :emergencia_parentesco,
                emergencia_telefono = :emergencia_telefono";

        if (!empty($data['password'])) {
            $sql .= ", password = :password";
        }
        if (!empty($data['foto_perfil'])) {
            $sql .= ", foto_perfil = :foto_perfil";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':apellido', $data['apellido']);
        $stmt->bindValue(':apodo', $data['apodo']);
        $stmt->bindValue(':telefono', $data['telefono']);
        $stmt->bindValue(':correo_personal', $data['correo_personal']);
        $stmt->bindValue(':linkedin', $data['linkedin']);
        $stmt->bindValue(':calle', $data['calle']);
        $stmt->bindValue(':numeracion', $data['numeracion']);
        $stmt->bindValue(':depto', $data['depto']);

        $stmt->bindValue(':region_id', !empty($data['region_id']) ? $data['region_id'] : null);
        $stmt->bindValue(':provincia_id', !empty($data['provincia_id']) ? $data['provincia_id'] : null);
        $stmt->bindValue(':comuna_id', !empty($data['comuna_id']) ? $data['comuna_id'] : null);

        $stmt->bindValue(':emergencia_nombre', $data['emergencia_nombre']);
        $stmt->bindValue(':emergencia_parentesco', $data['emergencia_parentesco']);
        $stmt->bindValue(':emergencia_telefono', $data['emergencia_telefono']);
        $stmt->bindValue(':id', $id);

        if (!empty($data['password'])) {
            $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        }
        if (!empty($data['foto_perfil'])) {
            $stmt->bindValue(':foto_perfil', $data['foto_perfil']);
        }

        return $stmt->execute();
    }

    // =========================================================
    // 3. MÉTODOS ADMINISTRATIVOS
    // =========================================================

    public function create($rut, $email, $password, $nombre, $apellido, $fecha_nacimiento, $cargo_id, $sucursal_id, $rol_id, $fecha_ingreso)
    {
        $sql = "INSERT INTO usuarios (rut, email, password, nombre, apellido, fecha_nacimiento, cargo_id, sucursal_id, rol_id, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $nacimiento = !empty($fecha_nacimiento) ? $fecha_nacimiento : null;
        return $stmt->execute([$rut, $email, password_hash($password, PASSWORD_DEFAULT), $nombre, $apellido, $nacimiento, $cargo_id, $sucursal_id, $rol_id, $fecha_ingreso]);
    }

    public function updateAsAdmin($id, $rut, $email, $nombre, $apellido, $fecha_nacimiento, $cargo_id, $sucursal_id, $rol_id, $fecha_ingreso)
    {
        $sql = "UPDATE usuarios SET rut=?, email=?, nombre=?, apellido=?, fecha_nacimiento=?, cargo_id=?, sucursal_id=?, rol_id=?, fecha_ingreso=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $nacimiento = !empty($fecha_nacimiento) ? $fecha_nacimiento : null;
        return $stmt->execute([$rut, $email, $nombre, $apellido, $nacimiento, $cargo_id, $sucursal_id, $rol_id, $fecha_ingreso, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>