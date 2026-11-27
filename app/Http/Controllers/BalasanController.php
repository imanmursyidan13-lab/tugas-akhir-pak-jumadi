<?php

namespace App\Http\Controllers;

use App\Models\Balasan;
use App\Models\Kritik;
use Illuminate\Http\Request;

/**
 * Balas komentar: user mana pun yang sudah login (termasuk admin) boleh membalas
 * komentar (kritik) milik user lain di sebuah film.
 */
class BalasanController extends Controller
{
    public function store(Request $request, Kritik $kritik)
    {
        $data = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $kritik->balasans()->create($data + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Balasan berhasil dikirim.')->withFragment('komentar-' . $kritik->id);
    }

    public function destroy(Request $request, Balasan $balasan)
    {
        // hanya pemilik balasan atau admin yang boleh menghapus
        abort_unless($balasan->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $balasan->delete();

        return back()->with('success', 'Balasan berhasil dihapus.');
    }
}
