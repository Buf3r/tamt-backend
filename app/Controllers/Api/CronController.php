<?php

namespace App\Controllers\Api;

use App\Models\AuctionModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class CronController extends ResourceController
{
    use ResponseTrait;

    public function closeExpired()
{
    try {
        $db = new AuctionModel;
        $now = date('Y-m-d H:i:s');

        $expired = $db->where('status', 'open')
                ->where('date_completed <', $now)
                ->where('date_completed IS NOT NULL', null, false)
                ->findAll();

        if (empty($expired)) {
            return $this->respond([
                'status' => 200,
                'messages' => ['success' => 'No expired auctions'],
                'closed' => 0,
            ]);
        }

        $count = 0;
        foreach ($expired as $auction) {
            $db->update($auction['auction_id'], ['status' => 'closed']);
            $count++;
        }

        return $this->respond([
            'status' => 200,
            'messages' => ['success' => "Closed $count auctions"],
            'closed' => $count,
        ]);
    } catch (\Throwable $e) {
        return $this->failServerError($e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    }
}
    public function testNotification()
{
    try {
        $fcm = new \App\Libraries\FCMNotification();
        
        $userDb = new \App\Models\UserModel;
        $users = $userDb->where('fcm_token IS NOT NULL', null, false)->first();
        
        if (!$users || !$users['fcm_token']) {
            return $this->respond(['error' => 'No users with FCM token found']);
        }

        $result = $fcm->sendNotificationDebug(
            fcmToken: $users['fcm_token'],
            title: '🔔 Prueba',
            body: 'Test',
        );

        return $this->respond([
            'status' => 200,
            'result' => $result,
            'token_used' => substr($users['fcm_token'], 0, 20) . '...',
        ]);
    } catch (\Throwable $e) {
        return $this->respond(['error' => $e->getMessage()]);
    }
}
}