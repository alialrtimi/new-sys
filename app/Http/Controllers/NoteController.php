<?php

// app/Http/Controllers/NoteController.php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::all();
        return inertia('Notes', ['notes' => $notes]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:7', // اللون يجب أن يكون بالصيغة #HEX
        ]);

        Note::create($request->all());
        return redirect()->route('notes.index')->with('success', 'تم إضافة الملاحظة بنجاح');
    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:7', // اللون يجب أن يكون بالصيغة #HEX
        ]);

        $note->update($request->all());
        return redirect()->route('notes.index')->with('success', 'تم تحديث الملاحظة بنجاح');
    }

    public function destroy(Note $note)
    {
        $note->delete();
        return redirect()->route('notes.index')->with('success', 'تم حذف الملاحظة بنجاح');
    }
}
