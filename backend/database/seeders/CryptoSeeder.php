<?php

namespace Database\Seeders;

use App\Models\Cryptocurrency;
use App\Models\PriceHistory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CryptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cryptos = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC', 'current_price' => 42500.00],
            ['name' => 'Ethereum', 'symbol' => 'ETH', 'current_price' => 2250.00],
            ['name' => 'Cardano', 'symbol' => 'ADA', 'current_price' => 0.85],
            ['name' => 'Solana', 'symbol' => 'SOL', 'current_price' => 150.00],
            ['name' => 'Polkadot', 'symbol' => 'DOT', 'current_price' => 8.50],
            ['name' => 'Ripple', 'symbol' => 'XRP', 'current_price' => 2.10],
            ['name' => 'Litecoin', 'symbol' => 'LTC', 'current_price' => 180.00],
            ['name' => 'Chainlink', 'symbol' => 'LINK', 'current_price' => 28.00],
            ['name' => 'Stellar', 'symbol' => 'XLM', 'current_price' => 0.12],
            ['name' => 'Dogecoin', 'symbol' => 'DOGE', 'current_price' => 0.32],
        ];

        foreach ($cryptos as $crypto) {
            $cryptocurrency = Cryptocurrency::create($crypto);

            // Generate 31 days of price history (minimum 300 entries)
            $startDate = Carbon::now()->subDays(30);
            for ($i = 0; $i < 31; $i++) {
                $date = $startDate->copy()->addDays($i);
                
                // Create 10 price entries per day
                for ($j = 0; $j < 10; $j++) {
                    $priceVariation = rand(-2, 2) / 100;
                    $price = $crypto['current_price'] * (1 + $priceVariation);
                    
                    PriceHistory::create([
                        'cryptocurrency_id' => $cryptocurrency->id,
                        'price' => round($price, 2),
                        'created_at' => $date->copy()->addHours($j * 2),
                        'updated_at' => $date->copy()->addHours($j * 2),
                    ]);
                }
            }
        }
    }
}
