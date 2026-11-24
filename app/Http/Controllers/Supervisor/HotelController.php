<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(): View
    {
        $hotels = Hotel::orderBy('lokasi')->orderBy('nama_hotel')->paginate(12);

        return view('supervisor.hotel.index', compact('hotels'));
    }

    public function create(): View
    {
        return view('supervisor.hotel.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_hotel'    => ['required', 'string', 'min:3', 'max:150'],
            'lokasi'        => ['required', 'in:Makkah,Madinah'],
            'bintang_hotel' => ['required', 'integer', 'min:1', 'max:5'],
            'alamat_hotel'  => ['nullable', 'string', 'max:255'],
            'foto_hotel'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('foto_hotel')) {
            $validated['foto_hotel'] = $this->saveHotelImage($request->file('foto_hotel'));
        }

        Hotel::create($validated);

        return redirect()->route('supervisor.hotel.index')
            ->with('success', 'Master hotel berhasil ditambahkan.');
    }

    public function edit(Hotel $hotel): View
    {
        return view('supervisor.hotel.edit', compact('hotel'));
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $request->validate([
            'nama_hotel'    => ['required', 'string', 'min:3', 'max:150'],
            'lokasi'        => ['required', 'in:Makkah,Madinah'],
            'bintang_hotel' => ['required', 'integer', 'min:1', 'max:5'],
            'alamat_hotel'  => ['nullable', 'string', 'max:255'],
            'foto_hotel'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('foto_hotel')) {
            $validated['foto_hotel'] = $this->saveHotelImage($request->file('foto_hotel'), $hotel->foto_hotel);
        } else {
            unset($validated['foto_hotel']);
        }

        $hotel->update($validated);

        return redirect()->route('supervisor.hotel.index')
            ->with('success', 'Data hotel berhasil diperbarui.');
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        if ($hotel->paketTipeKamars()->exists()) {
            return redirect()->route('supervisor.hotel.index')
                ->with('error', 'Hotel tidak dapat dihapus karena sedang digunakan pada paket.');
        }

        $this->deleteHotelImage($hotel->foto_hotel);
        $hotel->delete();

        return redirect()->route('supervisor.hotel.index')
            ->with('success', 'Master hotel berhasil dihapus.');
    }

    private function saveHotelImage(UploadedFile $file, ?string $oldPath = null): string
    {
        $targetDir = public_path('images/hotels');
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }

        $filename = Str::random(32) . '.' . $ext;
        $file->move($targetDir, $filename);

        $this->deleteHotelImage($oldPath);

        return 'images/hotels/' . $filename;
    }

    private function deleteHotelImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'images/hotels/')) {
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }
}
