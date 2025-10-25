<?php
// app/helpers/ImageUploader.php
namespace Barkios\helpers;

use Exception;

class ImageUploader {
    
    private $uploadDir;
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5242880; // 5MB
    private $errors = [];
    
    public function __construct(string $uploadDir = 'public/uploads/products/') {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        // Crear directorio si no existe
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Subir imagen de producto
     */
    public function upload(array $file, string $productId = null): array {
        $this->errors = [];
        
        // Validaciones básicas
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $this->errors[] = "No se recibió ningún archivo";
            return $this->getResponse(false);
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadError($file['error']);
            return $this->getResponse(false);
        }
        
        // Validar tamaño
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = "El archivo excede el tamaño máximo de " . 
                             $this->formatBytes($this->maxFileSize);
            return $this->getResponse(false);
        }
        
        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = "Extensión no permitida. Use: " . 
                             implode(', ', $this->allowedExtensions);
            return $this->getResponse(false);
        }
        
        // Validar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $this->errors[] = "Tipo de archivo no permitido";
            return $this->getResponse(false);
        }
        
        // Validar que sea imagen real
        if (!getimagesize($file['tmp_name'])) {
            $this->errors[] = "El archivo no es una imagen válida";
            return $this->getResponse(false);
        }
        
        // Generar nombre único
        $fileName = $productId ? "product_{$productId}_" : "product_";
        $fileName .= uniqid() . '.' . $extension;
        $destination = $this->uploadDir . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Redimensionar automáticamente
            $this->resize($destination, 800, 800);
            
            return $this->getResponse(true, [
                'filename' => $fileName,
                'path' => $destination,
                'url' => '/' . $destination,
                'size' => $file['size'],
                'mime_type' => $mimeType
            ]);
        }
        
        $this->errors[] = "Error al guardar el archivo";
        return $this->getResponse(false);
    }
    
    /**
     * Redimensionar imagen manteniendo proporción
     */
    public function resize(string $filePath, int $maxWidth = 800, int $maxHeight = 800): bool {
        if (!file_exists($filePath)) {
            return false;
        }
        
        list($width, $height, $type) = getimagesize($filePath);
        
        // Calcular nuevas dimensiones
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        
        if ($ratio >= 1) {
            return true; // No necesita redimensionar
        }
        
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Crear imagen desde archivo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        // Crear nueva imagen
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled($destination, $source, 0, 0, 0, 0, 
                          $newWidth, $newHeight, $width, $height);
        
        // Guardar
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $filePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $filePath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $filePath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($destination, $filePath, 90);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
    }
    
    /**
     * Eliminar imagen física del servidor
     */
    public function delete(string $fileName): bool {
        $filePath = $this->uploadDir . $fileName;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * Eliminar imagen por ruta completa
     */
    public function deleteByPath(string $path): bool {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
    
    // Métodos auxiliares
    
    private function getUploadError(int $code): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño permitido por PHP',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión detuvo la subida'
        ];
        
        return $errors[$code] ?? 'Error desconocido';
    }
    
    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    private function getResponse(bool $success, array $data = []): array {
        return [
            'success' => $success,
            'data' => $data,
            'errors' => $this->errors
        ];
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function setMaxFileSize(int $bytes): void {
        $this->maxFileSize = $bytes;
    }
}