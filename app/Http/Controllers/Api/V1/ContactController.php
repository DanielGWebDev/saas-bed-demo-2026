<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Http\Requests\DeleteContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ContactResource::collection(Contact::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create(array_merge(
            $request->validated(),
            ['user_id' => 1] // TODO: replace with auth()->id() when auth is implemented
        ));
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contact->update($request->validated());
        return new ContactResource($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteContactRequest $request, Contact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }
}
