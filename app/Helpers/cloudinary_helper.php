<?php

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

function uploadToCloudinary(string $filePath, string $folder = 'auction'): string
{
    log_message('debug', 'Cloudinary config: ' . getenv('CLOUDINARY_CLOUD_NAME') . ' key: ' . getenv('CLOUDINARY_API_KEY'));
    try {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => getenv('CLOUDINARY_API_KEY'),
                'api_secret' => getenv('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true]
        ]);

        $cloudinary = new Cloudinary();
        $result = $cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
        ]);

        return $result['secure_url'];
    } catch (\Exception $e) {
        log_message('error', 'Cloudinary upload error: ' . $e->getMessage());
        return '';
    }
}

function deleteFromCloudinary(string $imageUrl): void
{
    if (empty($imageUrl)) return;
    
    try {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => getenv('CLOUDINARY_API_KEY'),
                'api_secret' => getenv('CLOUDINARY_API_SECRET'),
            ],
        ]);

        $cloudinary = new Cloudinary();
        $parts = explode('/', $imageUrl);
        $filename = pathinfo(end($parts), PATHINFO_FILENAME);
        $folder = $parts[count($parts) - 2];
        $publicId = $folder . '/' . $filename;

        $cloudinary->uploadApi()->destroy($publicId);
    } catch (\Exception $e) {
    log_message('error', 'Cloudinary error: ' . $e->getMessage());
    throw new \Exception('Cloudinary error: ' . $e->getMessage());
    }
}