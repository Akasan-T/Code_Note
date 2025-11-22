<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Label;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Str;

class NoteController extends Controller
{
    public function index(Request $request) 
    {
        $labelId = $request->query('label');
        $keyword = $request->query('keyword');
        $converter = new CommonMarkConverter();

        $query = Note::with('labels');

        if($labelId){
            $query->whereHas('labels', function($q) use ($labelId) {
                $q->where('labels.id', $labelId);
            });
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword){
                $q ->where('title','like',"%{$keyword}%")
                    ->orWhere('content','like',"%{$keyword}%");
            });
        }



        $notes = $query->get();
        

        foreach ($notes as $note) {
            $note->html_preview = Str::limit(
                strip_tags($converter->convert($note->content)->getContent()),
                100,
                '...'
            );
        }

        $labels = Label::all();

        return view('note.index', compact('notes','notes','labels','labelId'));
    }

    public function create()
    {
        $labels = Label::all();
        return view('note.create', compact('labels'));
    }

    public function store(Request $request)
    {
        $note=Note::create([
            'title' => $request -> title,
            'content' => $request -> content,
            'user_id'=> auth()->id(),
        ]);

        if  ($request->has('labels')) {
            $note->labels()->attach($request->labels);
        }

        return redirect()->route('notes.index');
    }

    public function show(Note $note)
    {
        $converter = new CommonMarkConverter();
        $htmlContent = $converter->convert($note->content);
        $labels = Label::all();

        return view('note.show', ['note'=> $note, 'htmlContent' => $htmlContent, 'labels'=> $labels,]);
    }

    public function edit(Note $note)
    {
        $note ->load('labels');
        $labels = Label::all();
        return view('note.edit', compact('note','labels'));

    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'title' => 'required|string|max:255', 
            'content' => 'required|string',
        ]);

        $note->update([
            'title'=>$request->title,
            'content'=>$request->content,
        ]);

        $note->labels()->sync($request->labels ?? []);

        return redirect()->route('notes.show' ,$note)->with('success', 'ノートを更新しました');

    }

    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()->route('notes.index')
            ->with('success','ノートを消去しました');
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads','public');
            return $request()->json([
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['error' => 'No image uploaded'],400);
    }

}
