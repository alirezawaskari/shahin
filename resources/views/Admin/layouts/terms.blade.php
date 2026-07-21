@php
    $__termsUser = auth()->user();
    $__termsEmployee = $__termsUser ? $__termsUser->employee : null;
    $__termsCategory = optional(optional($__termsUser)->roles->first())->category;
    $__termsFile = isset($role_categories_with_terms) && $__termsCategory
        ? ($role_categories_with_terms[$__termsCategory]['role_category_terms_file_path'] ?? null)
        : null;
@endphp
{{-- Show only to users who ARE employees (the accept form updates ->employee), whose role
     category has a terms file, and who haven't agreed to the current version yet. --}}
@if($__termsEmployee && $__termsFile && $__termsEmployee->is_terms_accept != 1)
    <div class="alert alert-danger text-center">
        همکار گرامی جهت امکان ادامه فعالیت در سامانه لازم است حتما فایل قوانین را دانلود , مطالعه و موافقت با قوانین را ثبت نمایید.
        <span class="btn btn-outline-primary">
        <a target="_blank" href="{{$__termsFile}}">
               <i class="fa fa-download" style="margin-left: 2px"></i>
            دانلود</a>
    </span>
    </div>
    <div class="alert alert-warning text-center">
        <form method="post" action="{{route('employee.terms.accept')}}" >
            @csrf
            <div class="form-group">
                <div class="form-check">
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                        همکار گرامی ثبت موافقت با قوانین بدان معناست
                        که شما تعهد می‌دهید که قوانین و مقررات را به دقت مطالعه نموده اید و با تمامی آن‌ها موافقت می‌کنید. </div>
                    <input style="cursor: pointer" class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label style="cursor: pointer" class="form-check-label" for="terms">
                        قوانین را خوانده و قبول دارم
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">ثبت</button>
        </form>
    </div>
@endif
