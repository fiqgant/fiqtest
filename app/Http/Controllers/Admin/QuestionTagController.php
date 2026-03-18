<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionTag;
use Illuminate\Http\Request;

class QuestionTagController extends Controller
{
    public function index()
    {
        $tags = QuestionTag::withCount('questions')->orderBy('name')->get();
        return view('admin.question-tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:50', 'unique:question_tags,name']]);
        QuestionTag::create(['name' => trim($request->name)]);
        return back()->with('success', 'Tag created.');
    }

    public function destroy(QuestionTag $questionTag)
    {
        $questionTag->delete();
        return back()->with('success', 'Tag deleted.');
    }
}
