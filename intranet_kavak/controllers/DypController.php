<?php
class DypController
{
    public function index()
    {
        require 'views/dyp_upgrade.php';
    }

    public function searchVehicle()
    {
        header('Content-Type: application/json');

        $stock_id = $_POST['stock_id'] ?? '';

        if (empty(trim($stock_id))) {
            echo json_encode([
                'success' => false,
                'error' => 'Consulta vacía'
            ]);
            exit;
        }

        // TODO: REEMPLAZA ESTA URL POR LA URL DE TU SCRIPT DE GOOGLE PARA EL DYP
        $apps_script_url = "https://script.google.com/macros/s/AKfycbwXXX_PON_AQUI_LA_URL_DEL_DYP_XXX/exec?query=" . urlencode($stock_id);

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
            echo json_encode(['error' => 'Error al conectar con la base de datos de DyP.']);
        }
        exit;
    }
}
?>
