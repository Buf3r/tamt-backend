<?php

namespace App\Models;

use CodeIgniter\Model;

class AuctionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'auctions';
    protected $primaryKey       = 'auction_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields = [
    'item_id', 'user_id', 'final_price', 'winner_user_id',
    'status', 'date_completed', 'vip_start', 'vip_end',
];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

   public function getAuction($id = NULL, $status = 'open', $where = NULL, $allStatus = false, $page = 1, $city = NULL, $appOrigin = 'subastalo')
{
    $select = 'auctions.auction_id, items.item_id, items.user_id, item_name, description, items.initial_price, items.location, items.condition, items.category, items.app_origin, auctions.final_price, auctions.winner_user_id, auctions.status, auctions.date_completed, auctions.created_at';

        if ($allStatus) {
            $whereArray = [
                'items.deleted_at'    => NULL,
                'auctions.deleted_at' => NULL,
                'items.app_origin'    => $appOrigin,
            ];
        } else {
            $whereArray = [
                'status'              => $status,
                'items.deleted_at'    => NULL,
                'auctions.deleted_at' => NULL,
                'items.app_origin'    => $appOrigin,
            ];
        }

    // Filtro por ciudad
    if ($city) {
        $whereArray['items.location'] = $city;
    }

    if ($where) {
        foreach ($where as $key => $value) {
            $whereArray[$key] = $value;
        }
    }

    if ($id) {
        $whereArray[$this->primaryKey] = $id;
        return $this->setTable('items')
            ->select($select)
            ->join('auctions', 'auctions.item_id = items.item_id', 'inner')
            ->where($whereArray)->first();
    }

    return $this->setTable('items')
        ->select($select)
        ->join('auctions', 'auctions.item_id = items.item_id', 'inner')
        ->where($whereArray)
        ->orderBy('auctions.created_at', 'desc')
        ->findAll(limit: 20, offset: (max(1, $page) - 1) * 20);
}


    public function getBidAuctions($userId)
    {
        return $this->db->table('auctions')
            ->distinct()
            ->select([
                'auctions.auction_id',
                'auctions.item_id',
                'auctions.user_id',
                'auctions.final_price',
                'auctions.winner_user_id',
                'auctions.status',
                'auctions.date_completed',
                'auctions.created_at',
                'items.item_name',
                'items.description',
                'items.initial_price',
                'items.location',
                'items.condition',
                'items.category',
                'items.app_origin',
            ])
            ->join(
                'items',
                'items.item_id = auctions.item_id',
                'inner'
            )
            ->join(
                'bids',
                'bids.auction_id = auctions.auction_id',
                'inner'
            )
            ->where('bids.user_id', $userId)
            ->where('auctions.deleted_at', null)
            ->where('items.deleted_at', null)
            ->orderBy('auctions.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

}
