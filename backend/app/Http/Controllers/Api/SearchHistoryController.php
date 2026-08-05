<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = SearchHistory::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($history);
    }

    public function store(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'city' => 'nullable|string',
            'category' => 'nullable|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
        ]);

        $history = SearchHistory::create([
            'user_id' => $request->user()->id,
            'keyword' => $request->keyword,
            'city' => $request->city,
            'category' => $request->category,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
        ]);

        return response()->json($history, 201);
    }

    public function destroy(Request $request) {
        SearchHistory::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Historique vidé']);
    }
}
