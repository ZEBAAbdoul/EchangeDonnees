<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::latest('envoye_le');

        if ($request->filter === 'non_lus') {
            $query->nonLus();
        }

        return view('admin.contacts.index', [
            'contacts' => $query->paginate(15)->withQueryString(),
            'nonLus'   => Contact::nonLus()->count(),
        ]);
    }

    public function show(Contact $contact)
    {
        if (! $contact->estLu()) {
            $contact->update(['lu_le' => now()]);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')
            ->with('success', 'Message supprimé.');
    }
}