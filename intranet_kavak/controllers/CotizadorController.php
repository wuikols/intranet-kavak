<?php
class CotizadorController
{
    public function index()
    {
        require 'views/cotizador.php';
    }

    public function searchStock()
    {
        header('Content-Type: application/json');

        $stock_id = $_POST['stock_id'] ?? '';

        if (empty($stock_id)) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }

        // TODO: REEMPLAZA ESTA URL POR LA URL DE TU SCRIPT DE GOOGLE PARA EL COTIZADOR
        $apps_script_url = "https://script.google.com/macros/s/AKfycbwXXX_PON_AQUI_LA_URL_DEL_COTIZADOR_XXX/exec?stockId=" . urlencode($stock_id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apps_script_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode == 200 && $response) {
            echo $response;
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos de precios.']);
        }
        exit;
    }
}
?>
