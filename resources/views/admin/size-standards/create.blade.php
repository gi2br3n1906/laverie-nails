<x-layouts.app title="Create Size Standard">
    <div class="mb-8">
        <a class="text-sm font-semibold text-stone-900 hover:text-stone-800" href="{{ route('admin.size-standards.index') }}">← Back to standards</a>
        <h1 class="font-serif mt-3 text-3xl font-bold tracking-tight">Create size standard</h1>
        <p class="mt-3 text-stone-600">Add one canonical empirical size record to the database.</p>
    </div>

    <section class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <x-admin.size-standard-form
            :action="route('admin.size-standards.store')"
            submit-label="Create standard"
        />
    </section>
</x-layouts.app>