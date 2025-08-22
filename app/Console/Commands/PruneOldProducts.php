<?php

namespace App\Console\Commands;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneOldProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   

    /**
     * The console command description.
     *
     * @var string
     */
   protected $signature = 'products:prune-old';
    protected $description = 'Xóa vĩnh viễn các sản phẩm đã ở trong thùng rác quá 20 ngày';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::onlyTrashed()
        ->where('deleted_at', '<', now()->subDays(20))
        ->get();

        $count = 0;
        foreach ($products as $product) {
            // Xóa vĩnh viễn các biến thể trước
            $product->variants()->withTrashed()->forceDelete();
            // Xóa vĩnh viễn sản phẩm
            $product->forceDelete();
            $count++;
        }

        $this->info("Đã xóa vĩnh viễn $count sản phẩm trong thùng rác.");
    }
}
