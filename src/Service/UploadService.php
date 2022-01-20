<?php
namespace App\Service;

class UploadService
{
    protected string $uploadFolder;

    public function __construct()
    {
        // ROOT was define in the config folder
        $this->uploadFolder = ROOT . "/assets/images/upload/";
    }

    public function check(array $file): string
    {
        $errorMessage = "";
        $theFileOnServer = $file['tmp_name'];
        $autorizedMime = ["image/jpeg", "image/jpg", "image/gif", "image/png"];
        // test about mime type
        $testMime = mime_content_type($theFileOnServer);
        if (!in_array($testMime, $autorizedMime)) {
            $errorMessage = "le fichier n'est pas reconnu comme une image";
        }
        // test about size
        $fileSize = filesize($theFileOnServer);
        if (99000 < $fileSize) {
            $errorMessage = "le fichier est trop volumineux";
        }
        // test about uploaded file
        if (!is_uploaded_file($theFileOnServer)) {
            $errorMessage = "il y a eu une erreur d'upload du fichier";
        }
        return $errorMessage;
    }
    
    public function add(array $file): string
    {
        $path = "";
        // basename help to protect to files' attacks
        $theFileOnServer = $file['tmp_name'];
        $originalFileName = basename($file['name']);
        $ext = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $mainName = pathinfo($originalFileName, PATHINFO_FILENAME);
        $tmpCleanedName = preg_replace("/\s/", "-", $mainName);
        $cleanedName = trim($tmpCleanedName, "-");
        $finalName = $cleanedName . uniqid() . '.' . $ext;
        $destination = $this->uploadFolder . $finalName;
        $succesUpload = move_uploaded_file($theFileOnServer, $destination);
        if ($succesUpload) {
            $path = $finalName;
        }
        return $path;
    }

    public function delete(string $path): bool
    {
        if ($path) {
            $fullPath = $this->uploadFolder . $path;
            $ok = unlink($fullPath);
            // maybe on MS-Windows, unlink doesn't work correctly
        } else {
            $ok = false;
        }
        return $ok;
    }

    public function update(array $file, string $oldPath): string
    {
        $path = $this->add($file);
        $this->delete($oldPath);
        return $path;
    }
}