<?php

namespace App\Console\Commands;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneOldCategories extends Command
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
    protected $signature = 'categories:prune-old';
    protected $description = 'Xóa vĩnh viễn các danh mục đã ở trong thùng rác quá 20 ngày';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Category::onlyTrashed()
            ->where('deleted_at', '<', Carbon::now()->subDays(20))
            ->forceDelete();

        $this->info("Đã xóa vĩnh viễn $count trong thùng rác danh mục.");
    }
}
