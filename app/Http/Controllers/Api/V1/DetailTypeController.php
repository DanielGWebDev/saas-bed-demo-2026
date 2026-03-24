<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteDetailTypeRequest;
use App\Http\Requests\StoreDetailTypeRequest;
use App\Http\Requests\UpdateDetailTypeRequest;
use App\Http\Resources\DetailTypeResource;
use App\Models\DetailType;

class DetailTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DetailTypeResource::collection(DetailType::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDetailTypeRequest $request)
    {
        $detailType = DetailType::create($request->validated());
        return (new DetailTypeResource($detailType))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DetailType $detailType)
    {
        return new DetailTypeResource($detailType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetailTypeRequest $request, DetailType $detailType)
    {
        $detailType->update($request->validated());
        return new DetailTypeResource($detailType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteDetailTypeRequest $request, DetailType $detailType)
    {
        $detailType->delete();
        return response()->noContent();
    }
}
