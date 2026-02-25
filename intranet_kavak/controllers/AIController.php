<?php
class AIController {
    // 🔥 PEGA TU API KEY DE GOOGLE AI STUDIO AQUÍ:
    private $apiKey = 'AIzaSyBTuzFhFpypi7BzYTNXpsuNoxL-W4KeoAU'; 
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=';

    public function processRequest() {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $action = $_POST['ai_action'] ?? '';
        $text = $_POST['text'] ?? '';

        if(empty($text)) { echo json_encode(['success' => false, 'error' => 'El texto está vacío.']); exit; }

        $prompt = "";
        switch($action) {
            case 'improve': 
                $prompt = "Actúa como un editor experto. Mejora la redacción, gramática y ortografía del siguiente texto. Devuelve SOLO el texto corregido, sin comillas ni explicaciones extra:\n\n"; 
                break;
            case 'professional': 
                $prompt = "Actúa como un gerente de comunicaciones. Reescribe el siguiente texto con un tono corporativo, elegante y altamente profesional. Devuelve SOLO el texto reescrito:\n\n"; 
                break;
            case 'friendly': 
                $prompt = "Actúa como un líder empático de Recursos Humanos. Reescribe el siguiente texto con un tono amigable, cercano y motivador para el equipo de trabajo. Devuelve SOLO el texto reescrito:\n\n"; 
                break;
            case 'summarize_tldr': 
                $prompt = "Eres un asistente corporativo experto. Haz un resumen ejecutivo de este documento. Tu respuesta debe ser DIRECTAMENTE código HTML usando este formato estricto: <ul style='margin:0; padding-left:20px; color:#4B5563;'><li>Punto clave 1</li><li>Punto clave 2</li><li>Punto clave 3</li></ul>. El texto a resumir es:\n\n"; 
                break;
            default: 
                $prompt = "Mejora este texto:\n\n";
        }

        $fullPrompt = $prompt . $text;
        $data = [ "contents" => [ ["parts" => [["text" => $fullPrompt]]] ], "generationConfig" => [ "temperature" => 0.7, "maxOutputTokens" => 1024 ] ];

        $ch = curl_init($this->apiUrl . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        if(isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $aiText = $result['candidates'][0]['content']['parts'][0]['text'];
            $aiText = preg_replace('/```(?:html)?\s*/', '', $aiText); // Limpiar marcas de markdown
            echo json_encode(['success' => true, 'result' => trim($aiText)]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error de conexión con Gemini API.', 'details' => $response]);
        }
    }
}
?>