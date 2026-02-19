<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::all();
        return response()->json($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $contact = Contact::create($validated);
        return response()->json($contact, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = Contact::findOrFail($id); // will throw 404 if not found
        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, string $id)
    {
        // Find the contact by ID or fail with 404
        $contact = Contact::findOrFail($id);

        // Validate the request using UpdateContactRequest
        $validated = $request->validated();

        // Update the contact with the validated data
        $contact->update($validated);

        // Return the updated contact as JSON
        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteContactRequest $request, string $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->noContent();  // 204 No Content
    }
}
