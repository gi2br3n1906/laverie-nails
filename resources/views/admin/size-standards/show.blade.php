<x-layouts.app :title="'Size Standard '.$sizeStandard->size_name">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a class="text-sm font-semibold text-stone-900 hover:text-stone-800" href="{{ route('admin.size-standards.index') }}">← Back to standards</a>
            <p class="mt-5 text-sm font-semibold uppercase tracking-[0.2em] text-stone-600">Canonical standard</p>
            <h1 class="font-serif mt-2 text-4xl font-bold tracking-tight">Size {{ $sizeStandard->size_name }}</h1>
        </div>

        <a class="rounded-xl border border-stone-300 bg-white px-5 py-3 text-center font-semibold text-stone-700 transition hover:bg-stone-50" href="{{ route('admin.size-standards.edit', $sizeStandard) }}">
            Edit standard
        </a>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-8 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                'Thumb / Jempol' => $sizeStandard->jempol,
                'Index / Telunjuk' => $sizeStandard->telunjuk,
                'Middle / Tengah' => $sizeStandard->tengah,
                'Ring / Manis' => $sizeStandard->manis,
                'Pinky / Kelingking' => $sizeStandard->kelingking,
                'Tolerance' => $sizeStandard->tolerance,
            ] as $label => $value)
                <div class="rounded-2xl bg-stone-50 p-5">
                    <dt class="text-sm font-medium text-stone-500">{{ $label }}</dt>
                    <dd class="mt-2 text-2xl font-bold text-stone-900">{{ $value }} mm</dd>
                </div>
            @endforeach

            <div class="rounded-2xl bg-stone-50 p-5">
                <dt class="text-sm font-medium text-stone-500">Classification status</dt>
                <dd class="mt-2 text-lg font-bold {{ $sizeStandard->is_active ? 'text-emerald-700' : 'text-stone-600' }}">
                    {{ $sizeStandard->is_active ? 'Active' : 'Inactive' }}
                </dd>
            </div>
        </dl>

        <div class="mt-8 border-t border-stone-200 pt-6">
            <form method="POST" action="{{ route('admin.size-standards.destroy', $sizeStandard) }}">
                @csrf
                @method('DELETE')
                <button class="rounded-xl border border-stone-300 bg-stone-100 px-5 py-3 font-semibold text-stone-800 transition hover:bg-stone-200" type="submit">
                    Delete standard
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>