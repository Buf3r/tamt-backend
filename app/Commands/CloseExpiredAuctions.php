<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AuctionModel;

class CloseExpiredAuctions extends BaseCommand
{
    protected $group       = 'Auction';
    protected $name        = 'auction:close-expired';
    protected $description = 'Closes all auctions that have passed their date_completed';

    public function run(array $params)
    {
        $db = new AuctionModel;

        $now = date('Y-m-d H:i:s');

        $expired = $db->where('status', 'open')
            ->where('date_completed <', $now)
            ->whereNotNull('date_completed')
            ->findAll();

        if (empty($expired)) {
            CLI::write('No expired auctions found.', 'green');
            return;
        }

        $count = 0;
        foreach ($expired as $auction) {
            $db->update($auction['auction_id'], ['status' => 'closed']);
            $count++;
        }

        CLI::write("Closed $count expired auctions.", 'green');
    }
}