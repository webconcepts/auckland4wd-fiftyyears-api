<?php

namespace App\Http\Controllers;

use App\Item;

class TimelineController extends Controller
{
    /**
     * Get the full timeline, all published items grouped by year
     */
    public function index()
    {
        $items = Item::published()->orderBy('date');

        return [
            'data' => $items->get()->groupBy('approx_year'),
            'updated_at' => $items->max('updated_at')
        ];
    }

    /**
     * Get the timeline for a given year, items in cronological order
     */
    public function show($year)
    {
        $items = Item::published()
            ->where('approx_year', $year == 'none' ? null : $year)
            ->orderBy('date');

        return [
            'data' => $items->get(),
            'updated_at' => $items->max('updated_at')
        ];
    }
}
