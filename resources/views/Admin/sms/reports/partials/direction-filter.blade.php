{{-- «جهت پیام» filter select (همه / ارسالی / دریافتی); $colClass optional. --}}
<div class="{{ $colClass ?? 'col-12 col-md-2' }}">
    <label class="form-label small fw-bold">جهت پیام</label>
    <select name="direction" class="form-select form-select-sm">
        <option value="">همه</option>
        <option value="outbound" {{ request('direction') === 'outbound' ? 'selected' : '' }}>ارسالی</option>
        <option value="inbound" {{ request('direction') === 'inbound' ? 'selected' : '' }}>دریافتی</option>
    </select>
</div>
