<?php

namespace App\Http\Controllers\Drafts;

use App\User;
use App\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        return ['data' => Auth::user()->draftItems()->where('type', $this->type)->get()];
    }

    /**
     * Retrieve an individual draft item
     */
    public function show($obfuscatedId)
    {
        return ['data' => $this->getItem($obfuscatedId)];
    }

    /**
     * Add a new draft item
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);

        $item = Item::create($this->attributesToStore($request));

        $routeName = 'drafts.'.Item::types()[$this->type].'s.show';

        return response(['data' => $item], 201)
            ->header('Location', route($routeName, ['obfuscatedId' => $item->obfuscatedId()]));
    }

    /**
     * Attributes and their values to be stored when creating an item
     *
     * @param Request $request
     * @return array
     */
    protected function attributesToStore(Request $request)
    {
        return [
            'type' => $this->type,
            'title' => $request->input('title'),
            'user_id' => Auth::user()->id
        ];
    }

    /**
     * Update value(s) for an existing draft item
     */
    public function update($obfuscatedId, Request $request)
    {
        $item = $this->getItem($obfuscatedId);

        $validData = collect($this->validDataOrAbort(
            $request,
            $this->attributesToUpdateValidationRules()
        ));

        $item->update($validData->all());

        $givenApproximateDate = $validData
            ->only(['approx_day', 'approx_month', 'approx_year'])
            ->count();

        if (!Auth::user()->isEditor() && !$item->wasChanged('date') && $givenApproximateDate) {
            $item->setDateFromApproximateDate()
                ->save();
        }

        $this->onAfterUpdate($item, $request);

        return ['data' => $item];
    }

    /**
     * Attributes and their validation rules for updating an item
     *
     * @return array
     */
    protected function attributesToUpdateValidationRules()
    {
        return [
            'title' => 'nullable',
            'date' => 'nullable|date_format:"Y-m-d"',
            'approx_day' => 'nullable|integer|between:1,31',
            'approx_month' => 'nullable|integer|between:1,12',
            'approx_year' => 'nullable|integer|between:1969,2019',
            'description' => 'nullable',
        ];
    }

    /**
     * Do something with an item after an update, extend this in child
     * controllers to do more with different item types
     *
     * @param Item $item
     * @param Request $request
     */
    protected function onAfterUpdate(Item $item, Request $request)
    {
    }

    /**
     * Remove a draft item
     */
    public function destroy($obfuscatedId, Request $request)
    {
        $this->getItem($obfuscatedId)
            ->remove();
    }

    /**
     * Get the draft item and check the user is authorized to edit it
     *
     * @param int $obfuscatedId
     * @return App\Item
     */
    protected function getItem($obfuscatedId)
    {
        $item = Item::draft()
            ->where('type', $this->type)
            ->findOrFail(Item::actualId($obfuscatedId));

        $this->authorize('edit', $item);

        return $item;
    }
}
