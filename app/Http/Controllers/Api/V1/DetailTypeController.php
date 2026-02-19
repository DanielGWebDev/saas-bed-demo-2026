<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDetailTypeRequest;
use App\Http\Requests\UpdateDetailTypeRequest;
use App\Models\DetailType;
use Illuminate\Http\Request;

class DetailTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detailTypes = DetailType::all();
        return response()->json($detailTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDetailTypeRequest $request)
    {
        $validated = $request->validated();
        $detailType = DetailType::create($validated);
        return response()->json($detailType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $detailType = DetailType::findOrFail($id); // will throw 404 if not found
        return response()->json($detailType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDetailTypeRequest $request, string $id)
    {
        // Find the contact by ID or fail with 404
        $detailType = DetailType::findOrFail($id);

        // Validate the request using UpdateContactRequest
        $validated = $request->validated();

        // Update the contact with the validated data
        $detailType->update($validated);

        // Return the updated contact as JSON
        return response()->json($detailType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $detailType = DetailType::findOrFail($id);
        $detailType->delete();

        return response()->noContent();  // 204 No Content
    }
}
