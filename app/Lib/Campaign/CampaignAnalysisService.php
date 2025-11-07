<?php

namespace App\Lib\Campaign;

use App\Models\Campaign;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Class CampaignAnalysisService
 * @package App\Services
 *
 * This class handles the business logic for analyzing campaign performance.
 * (Fulfills SOLID principles - Instruction #3)
 */
class CampaignAnalysisService
{
    /**
     * محاسبه آمار مالی برای یک کمپین مشخص.
     *
     * @param Campaign $campaign
     * @return array
     */
    public function calculateCampaignStatistics(Campaign $campaign): array
    {
        // Eager load books to get their IDs
        $campaign->load('books:id');
        $bookIds = $campaign->books->pluck('id');

        if ($bookIds->isEmpty()) {
            return [
                'total_sales_count' => 0,
                'total_amount' => 0,
                'total_publisher_share' => 0,
                'total_discount' => 0,
                'total_tax' => 0,
                'average_amount' => 0,
            ];
        }

        // Build the base query for payments related to this campaign
        // (Uses Eloquent best practices - Instruction #6 & #10)
        $paymentsQuery = Payment::query()
            ->where('sale_platform', $campaign->platform->value)
            ->whereBetween('sale_date', [$campaign->start_date, $campaign->end_date])
            ->whereIn('book_id', $bookIds);

        // Execute optimized aggregate queries
        $stats = $paymentsQuery->select(
            DB::raw('COUNT(*) as total_sales_count'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(publisher_share) as total_publisher_share'),
            DB::raw('SUM(discount) as total_discount'),
            DB::raw('SUM(tax) as total_tax'),
            DB::raw('AVG(amount) as average_amount')
        )->first();

        // Return the results as a clean array
        return [
            'total_sales_count' => (int)$stats->total_sales_count,
            'total_amount' => (int)$stats->total_amount,
            'total_publisher_share' => (int)$stats->total_publisher_share,
            'total_discount' => (int)$stats->total_discount,
            'total_tax' => (int)$stats->total_tax,
            'average_amount' => (int)$stats->average_amount,
        ];
    }
}
