<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\JWTCI4;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
    }

    public function login()
{
    // DEBUG TEMPORAL
    return $this->respond([
        'env_content' => file_get_contents(ROOTPATH . '.env'),
        'cloudinary_getenv' => getenv('CLOUDINARY_CLOUD_NAME'),
        'cloudinary_env' => env('CLOUDINARY_CLOUD_NAME'),
    ]);
    // FIN DEBUG

    if (!$this->validate([
        'username' => 'required|min_length[4]',
        'password' => 'required|min_length[6]',
    ])) {
        return $this->failValidationErrors(\Config\Services::validation()->getErrors());
    }

    $db = new UserModel;
    $user = $db->where('username', $this->request->getVar('username'))->first();

    if (!$user) {
        return $this->failNotFound('User not found');
    }

    if (!password_verify($this->request->getVar('password'), $user['password_hash'])) {
        return $this->failValidationErrors(['password' => 'password is incorrect']);
    }

    $jwt = new JWTCI4;
    try {
        $token = $jwt->token(
            userId: $user['user_id'],
            username: $user['username'],
            name: $user['name'],
            email: $user['email'],
            phone: $user['phone']
        );
    } catch (\Exception $e) {
        return $this->failServerError($e->getMessage());
    }

    return $this->respond(['status' => 200, 'token' => $token]);
}
}
