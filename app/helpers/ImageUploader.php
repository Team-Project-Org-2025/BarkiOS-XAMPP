<?php
// app/helpers/ImageUploader.php
namespace Barkios\helpers;

class ImageUploader {
    private $uploadDir;
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5242880; // 5MB en bytes

    public function __construct() {
        // Construir ruta absoluta desde la raíz del proyecto
        $projectRoot = dirname(dirname(__DIR__)); // Sube 2 niveles desde app/helpers/
        $this->uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        
        error_log("ImageUploader - Directorio: " . $this->uploadDir);
        error_log("ImageUploader - Existe: " . (is_dir($this->uploadDir) ? 'SI' : 'NO'));
        
        // Crear directorio si no existe
        if (!is_dir($this->uploadDir)) {
            error_log("ImageUploader - Creando directorio...");
            if (!mkdir($this->uploadDir, 0777, true)) {
                error_log("ERROR: No se pudo crear el directorio: " . $this->uploadDir);
                throw new \Exception("No se pudo crear el directorio de imágenes");
            }
            error_log("ImageUploader - Directorio creado");
        }
        
        // Verificar permisos de escritura
        if (!is_writable($this->uploadDir)) {
            error_log("ERROR: No hay permisos de escritura en: " . $this->uploadDir);
            throw new \Exception("No hay permisos de escritura en el directorio de imágenes");
        }
        
        error_log("ImageUploader - Inicializado correctamente");
    }

    /**
     * Sube una imagen y devuelve la ruta relativa
     */
    public function upload($file, $productId) {
        error_log("=== ImageUploader::upload INICIO ===");
        error_log("Product ID: " . $productId);
        
        $errors = [];

        // Validar que se haya subido un archivo
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            error_log("ERROR: No se subió ningún archivo");
            $errors[] = 'No se ha subido ningún archivo';
            return ['success' => false, 'errors' => $errors];
        }
        error_log("✓ Archivo temporal existe: " . $file['tmp_name']);

        // Validar tamaño
        if ($file['size'] > $this->maxFileSize) {
            error_log("ERROR: Archivo muy grande: " . $file['size']);
            $errors[] = 'El archivo excede el tamaño máximo permitido (5MB)';
            return ['success' => false, 'errors' => $errors];
        }
        error_log("✓ Tamaño válido: " . $file['size'] . " bytes");

        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        error_log("Extensión detectada: " . $extension);
        if (!in_array($extension, $this->allowedExtensions)) {
            error_log("ERROR: Extensión no permitida");
            $errors[] = 'Tipo de archivo no permitido. Permitidos: ' . implode(', ', $this->allowedExtensions);
            return ['success' => false, 'errors' => $errors];
        }
        error_log("✓ Extensión válida");

        // Validar que sea una imagen real
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            error_log("ERROR: No es una imagen válida");
            $errors[] = 'El archivo no es una imagen válida';
            return ['success' => false, 'errors' => $errors];
        }
        error_log("✓ Imagen válida: " . $imageInfo['mime']);

        // Generar nombre único
        $filename = 'product_' . $productId . '_' . time() . '.' . $extension;
        $targetPath = $this->uploadDir . $filename;
        
        error_log("Nombre archivo: " . $filename);
        error_log("Ruta destino: " . $targetPath);
        error_log("Directorio escribible: " . (is_writable($this->uploadDir) ? 'SI' : 'NO'));

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $lastError = error_get_last();
            error_log("ERROR al mover archivo: " . print_r($lastError, true));
            $errors[] = 'Error al guardar el archivo. Verifica permisos del directorio.';
            return ['success' => false, 'errors' => $errors];
        }
        
        error_log("✓ Archivo movido exitosamente a: " . $targetPath);
        error_log("Archivo existe después de mover: " . (file_exists($targetPath) ? 'SI' : 'NO'));

        // Devolver ruta relativa SIN barra inicial
        $relativePath = 'public/uploads/products/' . $filename;
        
        error_log("Ruta relativa para BD: " . $relativePath);
        error_log("=== ImageUploader::upload FIN ===");

        return [
            'success' => true,
            'data' => [
                'url' => $relativePath,
                'filename' => $filename,
                'size' => $file['size'],
                'mime' => $imageInfo['mime'],
                'absolute_path' => $targetPath
            ],
            'errors' => []
        ];
    }

    /**
     * Elimina una imagen del servidor
     */
    public function delete($imagePath) {
        $fullPath = __DIR__ . '/../../' . ltrim($imagePath, '/');
        
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        
        return false;
    }
}