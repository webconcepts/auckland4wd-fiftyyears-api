<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;

abstract class ItemController extends Controller
{
    /**
     * @var int $type set a type for each child controller
     */
    //protected $type;

    /**
     * Retrieve a list of items
     */
    public function index()
    {
        return ['data' => Item::published()->where('type', $this->type)->get()];
    }

    /**
     * Retrieve an individual item
     */
    public function show($obfuscatedId)
    {
        return [
            'data' => Item::published()
                ->where('type', $this->type)
                ->findOrFail(Item::actualId($obfuscatedId))
        ];
    }

    /**
     * Publish a draft item
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'id' => 'integer|required'
        ]);

        $item = Item::draft()
            ->where('type', $this->type)
            ->findOrFail(Item::actualId($request->input('id')));

        $this->authorize('edit', $item);

        $item->publish();

        $routeName = Item::types()[$this->type].'s.show';

        return response(['data' => $item], 201)
            ->header('Location', route($routeName, ['obfuscatedId' => $item->obfuscatedId()]));
    }

    /**
     * Unpublish a published item (return to draft)
     */
    public function destroy($obfuscatedId, Request $request)
    {
        $item = Item::published()
            ->where('type', $this->type)
            ->findOrFail(Item::actualId($obfuscatedId));

        $this->authorize('edit', $item);

        $item->unpublish();

        $routeName = 'drafts.'.Item::types()[$this->type].'s.show';

        return response('', 200);
    }
}
