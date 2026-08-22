<?php

namespace App\Investment\Services;

use App\Models\Expense;
use App\Models\Visitor;
use App\Timeline\Services\TimelineService;
use Illuminate\Support\Facades\DB;

class InvestmentService
{
    public function __construct(
        private TimelineService $timelineService
    ) {}

    public function getExpensesForVisitor(string $visitorVin, int $tenantId)
    {
        return Expense::where('visitor_vin', $visitorVin)
            ->where('tenant_id', $tenantId)
            ->orderByDesc('expense_date')
            ->get();
    }

    public function recordExpense(array $data, string $visitorVin, int $tenantId): Expense
    {
        return DB::transaction(function () use ($data, $visitorVin, $tenantId) {
            $visitor = Visitor::where('vin', $visitorVin)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $visitor) {
                abort(404);
            }

            $expense = Expense::create([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'category' => $data['category'],
                'amount' => $data['amount'] ?? null,
                'expense_date' => $data['expense_date'],
            ]);

            $this->timelineService->appendEvent([
                'tenant_id' => $tenantId,
                'visitor_vin' => $visitorVin,
                'type' => 'user',
                'source' => 'Expense',
                'summary' => $this->buildEventSummary($expense),
            ]);

            return $expense;
        });
    }

    private function buildEventSummary(Expense $expense): string
    {
        $summary = sprintf('Expense logged: %s', $expense->category);

        if ($expense->amount) {
            $summary .= sprintf(' ($%s)', number_format($expense->amount, 2));
        }

        return $summary;
    }
}
