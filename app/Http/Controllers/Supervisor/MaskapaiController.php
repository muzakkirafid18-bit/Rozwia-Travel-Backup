<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\StoreMaskapaiRequest;
use App\Http\Requests\Supervisor\UpdateMaskapaiRequest;
use App\Models\Maskapai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaskapaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $maskapais = Maskapai::latest()->paginate(10);

        return view('supervisor.maskapai.index', compact('maskapais'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('supervisor.maskapai.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMaskapaiRequest $request): RedirectResponse
    {
        Maskapai::create($request->validated());

        return redirect()->route('supervisor.maskapai.index')
            ->with('success', 'Master maskapai berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Maskapai $maskapai): View
    {
        return view('supervisor.maskapai.edit', compact('maskapai'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMaskapaiRequest $request, Maskapai $maskapai): RedirectResponse
    {
        $maskapai->update($request->validated());

        return redirect()->route('supervisor.maskapai.index')
            ->with('success', 'Data maskapai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maskapai $maskapai): RedirectResponse
    {
        if ($maskapai->jadwals()->exists()) {
            return redirect()->route('supervisor.maskapai.index')
                ->with('error', 'Maskapai tidak dapat dihapus karena sudah terkait dengan jadwal keberangkatan.');
        }

        $maskapai->delete();

        return redirect()->route('supervisor.maskapai.index')
            ->with('success', 'Master maskapai berhasil dihapus.');
    }
}
