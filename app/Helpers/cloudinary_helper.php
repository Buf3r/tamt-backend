<?php

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

function uploadToCloudinary(string $filePath, string $folder = 'auction'): string
{
    $cloudinary = new Cloudinary(
        Configuration::instance([
            'cloud' => [
                'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => getenv('CLOUDINARY_API_KEY'),
                'api_secret' => getenv('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true]
        ])
    );

    $result = $cloudinary->uploadApi()->upload($filePath, [
        'folder' => $folder,
    ]);

    return $result['secure_url'];
}

function deleteFromCloudinary(string $imageUrl): void
{
    $cloudinary = new Cloudinary(
        Configuration::instance([
            'cloud' => [
                'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => getenv('CLOUDINARY_API_KEY'),
                'api_secret' => getenv('CLOUDINARY_API_SECRET'),
            ],
        ])
    );

    // Extraer public_id de la URL
    $parts = explode('/', $imageUrl);
    $filename = pathinfo(end($parts), PATHINFO_FILENAME);
    $folder = $parts[count($parts) - 2];
    $publicId = $folder . '/' . $filename;

    $cloudinary->uploadApi()->destroy($publicId);
}