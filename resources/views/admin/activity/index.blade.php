<x-admin-layout title="Journal d'activité">

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/70 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Date</th>
                        <th class="px-6 py-3 font-semibold">Utilisateur</th>
                        <th class="px-6 py-3 font-semibold">Action</th>
                        <th class="px-6 py-3 font-semibold">Élément</th>
                        <th class="px-6 py-3 font-semibold">Modifications</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($activities as $activity)
                        <tr class="align-top transition hover:bg-gray-50/70">
                            <td class="whitespace-nowrap px-6 py-3.5 text-gray-400">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-3.5 font-medium">{{ $activity->causer?->name ?? 'Système' }}</td>
                            <td class="px-6 py-3.5">
                                @php
                                    [$eventClass, $eventLabel] = match ($activity->event) {
                                        'created' => ['bg-emerald-50 text-emerald-600', 'Création'],
                                        'updated' => ['bg-blue-50 text-blue-600', 'Modification'],
                                        'deleted' => ['bg-red-50 text-red-600', 'Suppression'],
                                        default => ['bg-gray-100 text-gray-500', $activity->event ?? '—'],
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $eventClass }}">{{ $eventLabel }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-gray-500">
                                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                            </td>
                            <td class="max-w-md px-6 py-3.5">
                                @if ($attrs = data_get($activity->properties, 'attributes'))
                                    <details class="text-xs text-gray-500">
                                        <summary class="cursor-pointer font-medium text-primary-600">{{ count($attrs) }} champ{{ count($attrs) > 1 ? 's' : '' }}</summary>
                                        <dl class="mt-2 space-y-1">
                                            @foreach ($attrs as $key => $value)
                                                <div class="flex gap-2">
                                                    <dt class="shrink-0 font-semibold">{{ $key }} :</dt>
                                                    <dd class="break-all">{{ is_scalar($value) ? \Illuminate\Support\Str::limit((string) $value, 80) : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </details>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Aucune activité enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $activities->links() }}</div>

</x-admin-layout>
