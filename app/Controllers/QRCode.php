<?php
/**
 * Clase simple para generar códigos QR
 * Versión simplificada que usa servicios online
 */
class QRCode {
    
    /**
     * Generar imagen QR usando servicio online
     */
    public static function generate($data, $size = 200) {
        $encoded_data = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded_data}";
    }
    
    /**
     * Generar imagen QR y guardarla localmente
     */
    public static function generateAndSave($data, $filename = null, $size = 200) {
        if (!$filename) {
            $filename = 'qr_' . md5($data) . '.png';
        }
        
        $qr_url = self::generate($data, $size);
        $save_path = __DIR__ . '/../../storage/qr_images/' . $filename;
        
        // Crear directorio si no existe
        $dir = dirname($save_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Descargar y guardar la imagen
        $image_data = file_get_contents($qr_url);
        if ($image_data !== false) {
            file_put_contents($save_path, $image_data);
            return '../storage/qr_images/' . $filename;
        }
        
        return $qr_url; // Fallback a URL online
    }
    
    /**
     * Generar código QR con datos estructurados
     */
    public static function generateStructured($data_array) {
        $qr_data = json_encode($data_array);
        return self::generate($qr_data);
    }
}
?>
