@extends('templates.index')

@section('content')
<div class="container" style="padding:30px 15px; min-height:400px">
  <h3>Watchlist Saya</h3>
  <p class="text-muted">Film yang kamu simpan untuk ditonton nanti.</p>

  <div class="row">
    @forelse ($films as $film)
      <div class="col-sm-3 col-xs-6" style="margin-bottom:20px">
        <a href="{{ route('movies.show', $film) }}">
          <img src="{{ $film->poster_url }}" alt="{{ $film->title }}" style="width:100%; border-radius:4px">
          <p style="margin-top:6px">{{ $film->title }} <small class="text-muted">({{ $film->year }})</small></p>
        </a>
        <form action="{{ route('watchlist.toggle', $film) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-minus"></i> Hapus</button>
        </form>
      </div>
    @empty
      <div class="col-sm-12 text-center text-muted" style="padding:40px 0">
        Belum ada film yang disimpan. Yuk <a href="{{ route('movies') }}">cari film</a> dan simpan ke watchlist.
      </div>
    @endforelse
  </div>

  {{ $films->links() }}
</div>
@endsection
