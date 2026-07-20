<x-layouts.app title="Edit Size Standard">
    <div class="mb-8">
        <a class="text-sm font-semibold text-stone-900 hover:text-stone-800" href="{{ route('admin.size-standards.show', $sizeStandard) }}">← Back to standard</a>
        <h1 class="font-serif mt-3 text-3xl font-bold tracking-tight">Edit {{ $sizeStandard->size_name }}</h1>
        <p class="mt-3 text-stone-600">Update this database-backed empirical standard carefully.</p>
    </div>

    <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <x-admin.size-standard-form
            :action="route('admin.size-standards.update', $sizeStandard)"
            method="PUT"
            :size-standard="$sizeStandard"
            submit-label="Save changes"
        />
    </section>
</x-layouts.app>