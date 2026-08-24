@permission('transfer_to_business')
    <button type="button"
            class="btn btn-outline-primary business-transfer-bulk-action {{ $class ?? '' }}"
            data-business-transfer-bulk
            data-transfer-entity="{{ $entity }}"
            data-record-selector="{{ $recordSelector ?? '.business-transfer-record' }}">
        انتقال به کسب و کار
    </button>
@endpermission
