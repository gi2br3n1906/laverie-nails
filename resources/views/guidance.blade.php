<x-layouts.app title="Panduan Pengukuran">
    <section class="grid items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]">
        <div>
            <span class="inline-flex rounded-full border border-stone-300 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-stone-900 shadow-sm">Pengukuran manual • tanpa kamera</span>
            <h1 class="mt-6 max-w-3xl font-serif text-5xl font-semibold leading-tight tracking-tight text-stone-900 sm:text-6xl">Panduan Pengukuran Kuku yang Tenang & Akurat</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-600">Ukur lebar nail plate secara manual dalam milimeter. Ikuti tiga langkah sederhana agar rekomendasi press-on nails terasa nyaman dan proporsional.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a class="rounded-full bg-stone-900 px-6 py-3 text-center font-semibold text-white shadow-lg shadow-stone-300 transition hover:-translate-y-0.5 hover:bg-stone-800" href="{{ route('measurements.create') }}">Mulai pengukuran</a>
                <a class="rounded-full border border-stone-200 bg-white px-6 py-3 text-center font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-800" href="#video-panduan">Lihat video panduan</a>
            </div>
        </div>
        <div class="relative rounded-[2rem] border border-white bg-white/70 p-5 shadow-2xl shadow-stone-300/50 backdrop-blur">
            <svg class="h-auto w-full" viewBox="0 0 560 420" role="img" aria-labelledby="guide-illustration-title">
                <title id="guide-illustration-title">Ilustrasi pengukuran lebar nail plate</title>
                <rect width="560" height="420" rx="36" fill="#f5f5f4"/>
                <path d="M185 335c-25-80-33-178 13-235 18-22 54-20 70 4 30 46 36 141 28 231" fill="#e7e5e4" stroke="#292524" stroke-width="5"/>
                <path d="M223 125c12-18 30-18 43 0 18 27 22 76 13 111-6 23-22 36-36 36-15 0-31-13-36-36-9-35-3-84 16-111Z" fill="#fff" stroke="#57534e" stroke-width="5"/>
                <path d="M206 190h76" stroke="#1c1917" stroke-width="4" stroke-linecap="round"/>
                <path d="m218 179-12 11 12 11M270 179l12 11-12 11" fill="none" stroke="#1c1917" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="330" y="120" width="170" height="70" rx="18" fill="#fff"/>
                <text x="415" y="150" text-anchor="middle" fill="#292524" font-size="15" font-family="sans-serif" font-weight="700">UKUR BAGIAN TERLEBAR</text>
                <text x="415" y="174" text-anchor="middle" fill="#78716c" font-size="14" font-family="sans-serif">catat dalam milimeter</text>
                <path d="M330 155h-42" stroke="#57534e" stroke-width="3" stroke-dasharray="7 7"/>
            </svg>
        </div>
    </section>

    <section class="mt-24" aria-labelledby="steps-heading">
        <div class="max-w-2xl"><p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">Tiga langkah</p><h2 class="mt-3 font-serif text-4xl font-semibold" id="steps-heading">Persiapkan, ukur, lalu catat</h2></div>
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['01', 'Siapkan alat', 'Gunakan penggaris milimeter yang jelas. Pastikan kuku bersih dan tangan rileks di permukaan datar.'],
                ['02', 'Ukur nail plate', 'Posisikan penggaris melintang pada bagian nail plate yang paling lebar, dari sisi kiri ke sisi kanan.'],
                ['03', 'Catat lima jari', 'Masukkan hasil jempol hingga kelingking. Aktifkan tangan kiri jika kedua tangan memiliki ukuran berbeda.'],
            ] as [$number, $title, $description])
                <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm"><span class="grid size-12 place-items-center rounded-2xl bg-stone-200 font-bold text-stone-800">{{ $number }}</span><h3 class="mt-5 font-serif text-2xl font-semibold">{{ $title }}</h3><p class="mt-3 text-sm leading-7 text-stone-600">{{ $description }}</p></article>
            @endforeach
        </div>
    </section>

    <section class="mt-24 grid gap-8 rounded-[2rem] bg-stone-900 p-6 text-white sm:p-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center" id="video-panduan">
        <div><p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-300">Video tutorial</p><h2 class="mt-3 font-serif text-4xl font-semibold">Lihat tekniknya secara perlahan</h2><p class="mt-4 leading-7 text-stone-300">Placeholder video ini disiapkan untuk demonstrasi pengukuran manual sesuai metode penelitian.</p></div>
        <video class="aspect-video w-full rounded-2xl border border-white/10 bg-stone-800" controls preload="metadata" aria-label="Video panduan pengukuran kuku"><track kind="captions" srclang="id" label="Bahasa Indonesia"></video>
    </section>
</x-layouts.app>