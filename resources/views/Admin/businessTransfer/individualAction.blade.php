@permission('transfer_to_business')
    <button type="button"
            class="{{ $class ?? 'btn btn-outline-primary' }}"
            data-business-transfer-single
            data-transfer-entity="{{ $entity }}"
            data-record-id="{{ $recordId }}"
            data-record-ids="{{ collect($recordIds ?? [$recordId])->map(fn ($id) => (int) $id)->implode(',') }}"
            data-current-business-ids="{{ collect($currentBusinessIds ?? [])->map(fn ($id) => (int) $id)->implode(',') }}">
        {{ $label ?? 'انتقال به کسب و کار' }}
    </button>
@endpermission
