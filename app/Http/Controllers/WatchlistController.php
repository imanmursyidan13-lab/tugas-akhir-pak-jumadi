<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Http\Request;

/**
 * Watchlist = daftar film yang ingin ditonton, khusus milik user yang sedang login.
 */
class WatchlistController extends Controller
{
    // Halaman "Watchlist Saya"
    public function index(Request $request)
    {
        $films = Film::select('films.id', 'films.title', 'films.poster', 'films.year')
            ->join('watchlists', 'watchlists.film_id', '=', 'films.id')
            ->where('watchlists.user_id', $request->user()->id)
            ->orderByDesc('watchlists.created_at')
            ->paginate(18);

        return view('watchlist.index', compact('films'));
    }

    // Tombol simpan/batal simpan di halaman detail film (toggle)
    public function toggle(Request $request, Film $film)
    {
        $user = $request->user();
        $existing = $user->watchlists()->where('film_id', $film->id)->first();

        if ($existing) {
            $existing->delete();
            $message = 'Film dihapus dari watchlist.';
        } else {
            $user->watchlists()->create(['film_id' => $film->id]);
            $message = 'Film disimpan ke watchlist.';
        }

        return back()->with('success', $message);
    }
}
