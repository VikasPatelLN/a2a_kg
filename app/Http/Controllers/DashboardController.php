<?php

namespace App\Http\Controllers;

use App\Models\{Entity, Triple, Source, Document, Answer};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $entities = Entity::latest()->take(25)->get();
        $triples  = Triple::latest()->take(50)->get();
        $sources  = Source::latest()->get();
        $docs     = Document::latest()->take(10)->get();
        $answers  = Answer::latest()->take(10)->get();
        return view('dashboard', compact('entities','triples','sources','docs','answers'));
    }
}
