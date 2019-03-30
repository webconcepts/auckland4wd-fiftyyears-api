<?php

namespace App\Http\Controllers\Drafts;

use App\Item;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TimelineController extends Controller
{
    /**
     * Get the a timeline of draft items grouped by year
     */
    public function index(Request $request)
    {
        if (Auth::user()->isEditor() && $user = $request->input('user')) {
            if ($user == 'all') {
                $query = Item::draft();
            } else {
                $query = Item::draft()->where('user_id', User::actualId($user));
            }
        } else {
            $query = Auth::user()->draftItems();
        }

        return [
            'data' => $query->orderBy('date')->get()->groupBy('approx_year'),
            'updated_at' => $query->max('updated_at')
        ];
    }
}
