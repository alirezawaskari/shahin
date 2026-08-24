{{--
    فیلتر «کسب و کار» — یک انتخابگر آماده برای بخش فیلتر هر فهرست.

    Drop it inside a list's filter <form> and the server side is already done:

        @include('Admin.filter.businessSelect')

    Nothing else is needed, because every list that is business-aware scopes through
    FilterHelper::getDataByCheckGhorfeExistOrNot() and THAT is what reads this select back
    (FilterHelper::GHORFE_REQUEST_KEY). The panel's filter forms are serialised whole, so the
    value travels with the rest of the filters on both the ajax lists and the plain GET ones.

    Renders nothing at all unless the signed-in user is لاله staff (admin / superAdmin) with
    no business of their own — a business admin is already pinned to theirs and must not be
    handed anybody else's. Only فعال businesses are listed (GhorfeOnlineList::active()).

    Optional: pass $wrapper_class to fit a form whose grid is not the usual col-md-3, e.g.
        @include('Admin.filter.businessSelect', ['wrapper_class' => 'col-12 col-md-4 mb-2'])
--}}
@php($business_filter_options = \App\Helpers\FilterHelper::ghorfeFilterOptions())
@php($business_filter_selected = request(\App\Helpers\FilterHelper::GHORFE_REQUEST_KEY))

@if($business_filter_options->isNotEmpty())
    <div class="{{ $wrapper_class ?? 'col-12 col-md-3 mb-2' }}">
        {{-- select2 (with its search box) is applied at runtime by select2-init.js --}}
        <select name="{{ \App\Helpers\FilterHelper::GHORFE_REQUEST_KEY }}" class="custom-select select2">
            <option value="all">انتخاب کسب و کار</option>
            @foreach($business_filter_options as $business_filter_option)
                <option value="{{ $business_filter_option->id }}"
                        @if((string) $business_filter_selected === (string) $business_filter_option->id) selected @endif>
                    {{ $business_filter_option->title }}
                </option>
            @endforeach
        </select>
    </div>
@endif
