<?php
class FormularioPraviaController
{
    public function index()
    {
        require 'views/formulario_pravia.php';
    }

    public function submit()
    {
        header('Content-Type: application/json');

        // Receives the JSON payload from the frontend fetch()
        $jsonPayload = file_get_contents('php://input');

        // TODO: REEMPLAZA ESTA URL POR LA URL DE TU SCRIPT DE GOOGLE PARA PRAVIA
        $apps_script_url = "https://script.google.com/macros/s/AKfycbwXXX_PON_AQUI_LA_URL_DE_PRAVIA_XXX/exec";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apps_script_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        // Important for POSTing to GAS JSON parser
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpcode == 200 && $response) {
            echo $response;
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Error al conectar con el servidor de Pravia. ' . $error]);
        }
        exit;
    }
}
?>
