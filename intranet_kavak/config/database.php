<?php
class Database
{
    private $host = "gateway01.us-east-1.prod.aws.tidbcloud.com";
    private $db_name = "github_sample";
    private $username = "2px7t9j3rxyEpdk.root";
    private $password = "FlsaBd0oCzpIcnkv";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            // TiDB Serverless requires port 4000 and STRICT SSL connection
            $options = array(
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            );

            // Detect internal Vercel/Linux Certificates paths dynamically
            if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
            }
            else if (file_exists('/etc/pki/tls/certs/ca-bundle.crt')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
            }
            else if (file_exists(__DIR__ . '/cacert.pem')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = __DIR__ . '/cacert.pem';
            }

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