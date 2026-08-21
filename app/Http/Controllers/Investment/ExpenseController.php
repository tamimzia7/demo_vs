<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Investment\LogExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Investment\Services\InvestmentService;

class ExpenseController extends Controller
{
    public function __construct(
        private InvestmentService $investmentService
    ) {}

    public function index(string $vin)
    {
        $expenses = $this->investmentService->getExpensesForVisitor(
            $vin,
            auth()->user()->tenant_id
        );

        return ExpenseResource::collection($expenses);
    }

    public function store(LogExpenseRequest $request, string $vin)
    {
        $expense = $this->investmentService->recordExpense(
            $request->validated(),
            $vin,
            auth()->user()->tenant_id
        );

        return new ExpenseResource($expense);
    }
}
