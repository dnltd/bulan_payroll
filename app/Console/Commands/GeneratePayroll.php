<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayrollService;
use Carbon\Carbon;

class GeneratePayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example usage:
     * php artisan payroll:generate
     *
     * @var string
     */
    protected $signature = 'payroll:generate {--start= : Start date (Y-m-d)} {--end= : End date (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate payroll for a specific period (defaults to current week)';

    /**
     * Payroll service instance.
     *
     * @var PayrollService
     */
    protected PayrollService $payrollService;

    /**
     * Create a new command instance.
     */
    public function __construct(PayrollService $payrollService)
    {
        parent::__construct();
        $this->payrollService = $payrollService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Determine start and end dates
        if ($this->option('start') && $this->option('end')) {
            $startDate = Carbon::parse($this->option('start'))->startOfDay();
            $endDate = Carbon::parse($this->option('end'))->endOfDay();
        } else {
            // Default to Saturday–Friday week
            $startDate = Carbon::now()->startOfWeek(Carbon::SATURDAY);
            $endDate = $startDate->copy()->addDays(6)->endOfDay();
        }

        $this->info("Generating payroll from {$startDate->toDateString()} to {$endDate->toDateString()}...");

        // Call the service
        $this->payrollService->generatePayroll($startDate, $endDate);

        $this->info('✅ Payroll generation completed successfully.');
    }
}
