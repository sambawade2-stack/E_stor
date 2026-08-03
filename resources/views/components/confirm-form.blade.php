@props(['action', 'method' => 'DELETE', 'message' => 'Cette action est irréversible. Voulez-vous continuer ?'])

{{--
    Remplace un onsubmit="return confirm(...)" par la modale premium
    globale (voir partials.confirm-modal, incluse dans admin-layout).
--}}
<form action="{{ $action }}" method="POST" x-data
      @submit.prevent="window.dispatchEvent(new CustomEvent('open-confirm', { detail: { message: @js($message), form: $el } }))"
      {{ $attributes }}>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif
    {{ $slot }}
</form>
