<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisor\StoreRekeningPerusahaanRequest;
use App\Http\Requests\Supervisor\UpdateRekeningPerusahaanRequest;
use App\Models\RekeningPerusahaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekeningPerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $rekenings = RekeningPerusahaan::latest()->paginate(10);

        return view('supervisor.rekening.index', compact('rekenings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('supervisor.rekening.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRekeningPerusahaanRequest $request): RedirectResponse
    {
        RekeningPerusahaan::create($request->validated());

        return redirect()->route('supervisor.rekening.index')
            ->with('success', 'Rekening bank perusahaan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RekeningPerusahaan $rekening): View
    {
        return view('supervisor.rekening.edit', compact('rekening'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRekeningPerusahaanRequest $request, RekeningPerusahaan $rekening): RedirectResponse
    {
        $rekening->update($request->validated());

        return redirect()->route('supervisor.rekening.index')
            ->with('success', 'Data rekening perusahaan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RekeningPerusahaan $rekening): RedirectResponse
    {
        if ($rekening->invoices()->exists()) {
            return redirect()->route('supervisor.rekening.index')
                ->with('error', 'Rekening tidak dapat dihapus karena sudah memiliki riwayat invoice.');
        }

        $rekening->delete();

        return redirect()->route('supervisor.rekening.index')
            ->with('success', 'Rekening bank perusahaan berhasil dihapus.');
    }
}
