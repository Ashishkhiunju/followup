<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanSentReminder;
use App\Http\Resources\SentReminderResource;

class ReminderController extends Controller
{

    public const PER_PAGE = 10;
    public const DEFAULT_SORT_FIELD = 'created_at';
    public const DEFAULT_SORT_ORDER = 'asc';


    public function index(Request $request){
        $sortFields = ['reminder_date', 'reminder_type'];
        $sortFieldInput = $request->input('sort_field', self::DEFAULT_SORT_FIELD);
        $sortField = in_array($sortFieldInput, $sortFields) ? $sortFieldInput : self::DEFAULT_SORT_FIELD;
        $sortOrder = $request->input('sort_order', self::DEFAULT_SORT_ORDER);
        $searchInput = $request->input('search');
        $query = LoanSentReminder::with('loan','loan.customer')->orderBy("created_at", 'desc');
        $perPage = $request->input('per_page') ?? self::PER_PAGE;
        if (!is_null($searchInput)) {
            $searchQuery = "%$searchInput%";
            $query = $query->where('reminder_date', 'like', $searchQuery)->orWhere('reminder_type', 'like', $searchQuery);
        }
        $customers = $query->paginate((int) $perPage);
        return SentReminderResource::collection($customers);
    }
}
