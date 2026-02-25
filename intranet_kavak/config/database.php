<?php
class Database
{
    private $host = "gateway01.us-east-1.prod.aws.tidbcloud.com";
    private $db_name = "github_sample";
    private $username = "2px7t9j3rxyEpdk.root";
    private $password = "RpUhW1ZeMbB/PuG6";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            // TiDB Serverless requires port 4000 and SSL connection
            $options = array(
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            );
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=4000;dbname=" . $this->db_name, $this->username, $this->password, $options);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
