<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Label;

class LabelController extends Controller
{
    public function create(Request $request)
    {
        $label = null;
        $query = Label::query();

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $labels = $query->get();
        return view('label.create', compact('labels', 'label'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:labels,name',
        ]);

        Label::create([
            'name' => $request->name,
            'color' => $request->color ?? '#cccccc',
        ]);

        return redirect()->route('label.create')->with('success', 'ラベルを追加しました');
    }

    public function edit(Label $label)
    {
        return view('label.edit', compact('label'));
    }

    public function update(Request $request, Label $label)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:labels,name,' . $label->id,
            'color' => 'nullable|string|max:7',
        ]);

        $label->update([
            'name' => $request->name,
            'color' => $request->color ?? $label->color,
        ]);

        return redirect()->route('label.create')->with('success', 'ラベルを更新しました');
    }

    public function destroy(Label $label)
    {
        $label->delete();
        return redirect()->route('label.create')->with('success', 'ラベルを消去しました');
    }

    public function index(Request $request)
    {
        $query = Label::query();

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        $labels = $query->get()->map(function ($label) {
            $label->formatted_created_at = $label->created_at
                ? $label->created_at->format('Y-m-d H:i')
                : '';
            return $label;
        });

        return view('label.create', compact('labels'));
    }
}