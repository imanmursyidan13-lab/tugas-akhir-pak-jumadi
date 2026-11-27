# One Movies — Laravel kelas XII

Aplikasi katalog film (Laravel 13, Bootstrap 3) dengan **dashboard admin**, **CRUD**, **login/register**, **relasi model**, dan gambar poster yang disimpan di **storage**.

## Cara menjalankan

```bash
git clone nama-repo-masing-masing
cd laravel-kelas-xii            # masuk ke folder proyek
cp .env.example .env            # lalu edit DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate:fresh --seed   # buat tabel + isi data (genre, film, user, admin, dll)
php artisan storage:link           # WAJIB: symlink public/storage -> storage/app/public
php artisan serve
```

> Database harus sudah dibuat dulu (mis. lewat phpMyAdmin) dan namanya sama dengan `DB_DATABASE` di `.env`.

### Akun bawaan seeder

| Role  | Email               | Password   |
|-------|---------------------|------------|
| admin | admin@example.com   | 12345678   |
| user  | (email acak dari factory, lihat tabel `users`) | 12345678 |

Login lewat tombol **Login** di pojok kanan atas. Admin otomatis diarahkan ke `/admin`.

## Pengerjaan "Tugas Pakjum"

### 1. Dashboard admin — `/admin`
* Route group `admin` di `routes/web.php` dengan middleware `auth` + `admin`.
* Middleware `admin` = `app/Http/Middleware/EnsureUserIsAdmin.php` (didaftarkan di `bootstrap/app.php`). User biasa mendapat error 403.
* `Admin\DashboardController` menampilkan jumlah data, film & kritik terbaru, dan jumlah film per genre.
* Layout: `resources/views/admin/layouts/app.blade.php`.

### 2. CRUD lengkap (menu di sidebar admin)
| Menu   | Controller                     | URL                |
|--------|--------------------------------|--------------------|
| Film   | `FilmController` (+ upload poster) | `/admin/films`  |
| Genre  | `Admin\GenreController`        | `/admin/genres`    |
| Cast   | `CastController`               | `/admin/casts`     |
| Peran  | `PeranController`              | `/admin/perans`    |
| Kritik | `KritikController`             | `/admin/kritiks`   |
| User (+ profile) | `Admin\UserController` | `/admin/users`     |

Validasi ada di `app/Http/Requests/*` (Store/Update). User biasa juga bisa mengomentari film (`/movies/{film}`) dan mengubah profilnya sendiri (`/profile`).

### 3. Login & auth
* `Auth\AuthController`: `login`, `register`, `logout` (form-nya ada di modal `templates/component/login.blade.php`).
* Password otomatis di-hash (cast `hashed` di model `User`), session di-regenerate saat login.
* Halaman yang butuh login (`/admin`, `/profile`, kirim komentar) akan mengarahkan ke modal login.

### 4. Relasi di model
| Model   | Relasi |
|---------|--------|
| `Film`  | `genre()` belongsTo, `perans()` hasMany, `kritiks()` hasMany, `actors()` belongsToMany Cast (lewat `perans`) |
| `Genre` | `films()` hasMany |
| `Cast`  | `perans()` hasMany, `films()` belongsToMany |
| `Peran` | `film()` belongsTo, `cast()` belongsTo |
| `Kritik`| `film()` belongsTo, `user()` belongsTo |
| `User`  | `role()`, `profile()` belongsTo, `kritiks()` hasMany, `isAdmin()` |
| `Profile` / `Role` | `user()` hasOne / `users()` hasMany |

Controller memakai eager loading (`with(...)`) supaya tidak N+1 query.

### 5. Memindahkan images dari `public` ke `storage` — step by step
Sudah dikerjakan di proyek ini. Kalau mau mengulang manual, urutannya:

1. **Buat folder tujuan**: `storage/app/public/images`
2. **Pindahkan file poster**: semua `*.jpg` dari `public/template/images/` ke `storage/app/public/images/`
   (kecuali `1.jpg`, `arrow1.png`, `img-sp.png`, `play-button.png` yang dipanggil oleh CSS template — itu tetap di `public`).
3. **Buat symlink**: `php artisan storage:link` → `public/storage` menunjuk ke `storage/app/public`, jadi file bisa diakses lewat `http://localhost:8000/storage/images/nama.jpg`.
4. **Samakan path di database**: kolom `films.poster` sekarang berisi `images/nama.jpg` (lihat `database/data/movies.csv`). Jalankan ulang `php artisan migrate:fresh --seed`.
5. **Pakai accessor di view**: `{{ $film->poster_url }}` (didefinisikan di `Film::posterUrl()`) — tidak perlu lagi menulis `asset('storage/...')` atau `/{{ $film->poster }}` di setiap view.
6. **Upload baru**: admin mengunggah poster → `store('images', 'public')` menyimpan ke `storage/app/public/images`; file lama ikut dihapus saat film diganti/dihapus (`Film::deletePosterFile()`).
7. `storage/app/public/.gitignore` sudah diubah agar folder `images/` ikut ter-commit ke Git.

## Login & pembagian akses (user vs admin)

* **Tamu (belum login)**: hanya bisa melihat daftar film, detail film, dan cari film. Tidak bisa komentar, membalas, atau menyimpan watchlist — tombolnya diarahkan ke modal login.
* **User biasa (role `user`)**: semua yang tamu bisa, ditambah: menulis komentar + rating di film, membalas komentar, menyimpan film ke watchlist, dan mengedit profil sendiri. Tidak bisa membuka `/admin` (akan mendapat error 403 lewat middleware `admin`).
* **Admin (role `admin`)**: semua yang user bisa, ditambah akses penuh ke `/admin` (dashboard, CRUD Film/Genre/Cast/Peran/Kritik/User), dan boleh menghapus balasan komentar siapa pun (user biasa hanya boleh hapus balasannya sendiri).

Pengecekan role ada di `User::isAdmin()` dan middleware `app/Http/Middleware/EnsureUserIsAdmin.php`.

## Fitur tambahan: balas komentar & watchlist

**Balas komentar** (`app/Models/Balasan.php`, `BalasanController`, tabel `balasans`)
* Setiap komentar (`Kritik`) bisa punya banyak balasan (`Kritik::balasans()` hasMany).
* Form balas muncul di bawah tiap komentar di halaman detail film, hanya untuk user yang login.
* User boleh hapus balasannya sendiri; admin boleh hapus balasan siapa pun.

**Watchlist / simpan film** (`app/Models/Watchlist.php`, `WatchlistController`, tabel `watchlists`)
* Tombol "Simpan ke Watchlist" di halaman detail film (toggle simpan/hapus).
* Halaman `/watchlist` ("Watchlist Saya", link ada di header) menampilkan semua film yang disimpan user yang sedang login.
* Relasi: `User::watchlists()` hasMany, `Watchlist::film()` / `Watchlist::user()` belongsTo. Kombinasi `user_id` + `film_id` dibuat `unique` di migration supaya 1 film tidak tersimpan dobel.

> Setelah menarik update ini, jalankan `php artisan migrate` (tidak perlu `--fresh` kalau data lama ingin dipertahankan) supaya tabel `balasans` dan `watchlists` ikut terbuat.

## Perbaikan bug yang dilakukan
* `Profile` tidak punya `$fillable` dan `User` tidak punya `role_id`/`profile_id` di `$fillable` → seeder `AdminSeeder`/`ProfileSeeder` error.
* `ApiResponseClass` memakai `DB`, `Log`, `HttpResponseException` tanpa `use`.
* `User` belum memakai trait `HasApiTokens` padahal route API memakai `auth:sanctum`.
* `AppServiceProvider` query ke DB setiap boot (error sebelum migrate) → diganti view composer khusus navbar.
* Dropdown genre di navbar melewatkan genre pertama (loop mulai dari index 1).
* `@stack('script')` salah tempat dan `@push('scripts')` tidak pernah dirender; script `simplePlayer.js` memanggil file yang tidak ada.
* Link footer menuju file `.html` yang tidak ada; `orderByDesc('created_at', 'asc')` salah pemakaian; `Peran::all()->where()` memuat seluruh tabel.
* Halaman detail menampilkan `cast_id` & `user_id` mentah → sekarang nama cast & nama user (lewat relasi).
* `.env.example` memakai SQLite padahal README memakai MySQL → disamakan ke MySQL.
* Kolom pencarian di header sekarang berfungsi (`/movies?q=judul`).

## Catatan
* Setelah `git pull`/ganti struktur DB, jalankan `php artisan migrate:fresh --seed`.
* Kalau gambar tidak muncul: pastikan `php artisan storage:link` sudah dijalankan dan folder `storage/app/public/images` berisi file.
