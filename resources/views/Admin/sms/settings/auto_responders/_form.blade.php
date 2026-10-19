@php($rule = $rule ?? null)
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">اطلاعات قانون</h5>
            </div>
            <div class="card-body">
                @if(auth()->user()?->isSuperAdmin())
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                @endif

                <div class="mb-3">
                    <label class="form-label fw-bold">نام قانون <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="مثال: ارسال قیمت‌ها" value="{{ old('name', $rule?->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-7">
                        <label class="form-label fw-bold">کلمه کلیدی (محرک) <span class="text-danger">*</span></label>
                        <input type="text" name="trigger_keyword" class="form-control @error('trigger_keyword') is-invalid @enderror" placeholder="مثال: قیمت" value="{{ old('trigger_keyword', $rule?->trigger_keyword) }}" required>
                        @foreach($errors->get('trigger_keyword') as $msg)
                            <div class="invalid-feedback d-block">{{ $msg }}</div>
                        @endforeach
                        <div class="form-text">فاصله‌های اضافه، حروف بزرگ/کوچک و ارقام فارسی/عربی هنگام مقایسه یکسان در نظر گرفته می‌شوند.</div>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-bold">نوع تطبیق <span class="text-danger">*</span></label>
                        <select name="match_type" class="form-select @error('match_type') is-invalid @enderror" required>
                            <option value="exact" {{ old('match_type', $rule?->match_type ?? 'exact') === 'exact' ? 'selected' : '' }}>مطابقت کامل (متن پیامک دقیقا همین باشد)</option>
                            <option value="starts_with" {{ old('match_type', $rule?->match_type) === 'starts_with' ? 'selected' : '' }}>شروع با (متن پیامک با این کلمه شروع شود)</option>
                        </select>
                        @error('match_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">متن پاسخ <span class="text-danger">*</span></label>
                    <textarea name="reply_text" class="form-control @error('reply_text') is-invalid @enderror" rows="5" maxlength="1000" placeholder="متن پیامکی که برای مشتری ارسال می‌شود..." required>{{ old('reply_text', $rule?->reply_text) }}</textarea>
                    @error('reply_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">می‌توانید از متغیرهای پویا مانند {نام کاربر} و {نام خانوادگی} استفاده کنید. هزینه هر پاسخ از اعتبار پیامک کسب‌وکار کسر می‌شود.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-7">
                        <label class="form-label fw-bold">خط ارسال پاسخ</label>
                        <select name="sender_number_id" class="form-select @error('sender_number_id') is-invalid @enderror">
                            <option value="">— خطی که مشتری به آن پیامک داده (پیش‌فرض) —</option>
                            @foreach($senders as $s)
                                <option value="{{ $s->id }}" {{ (string) old('sender_number_id', $rule?->sender_number_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->number }}</option>
                            @endforeach
                        </select>
                        @error('sender_number_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-5 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive" value="1" {{ old('is_active', $rule ? (int) $rule->is_active : 1) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="isActive">قانون فعال باشد</label>
                        </div>
                    </div>
                </div>

                <div class="text-end pt-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-check me-1"></i> ذخیره قانون</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="fa fa-lock me-1"></i> کدهای رزروشده</h5>
            </div>
            <div class="card-body small">
                <p class="text-muted">این کلمات و کدها توسط سیستم یا اپراتورها تفسیر می‌شوند و نمی‌توانند محرک پاسخ خودکار باشند (و هرگز خودکار پاسخ داده نمی‌شوند):</p>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach(\App\Services\Sms\SmsReservedCodes::staticEntries() as $code => $meta)
                        <span class="badge bg-light text-dark border font-monospace" title="{{ $meta['source'] }}">{{ $code }}</span>
                    @endforeach
                </div>
                <p class="text-muted mb-0">همچنین شناسه‌های خدمات و کدهای پیامکی کاربران، نمایندگی‌ها و کسب‌وکارها ({{ number_format(count(array_filter($reserved, fn ($m) => in_array($m['type'], ['service_id', 'sms_code'], true)))) }} مورد) رزرو هستند. هر پیامی در هر کسب‌وکار فقط یک قانون فعال دارد؛ در صورت تداخل پیام خطا نمایش داده می‌شود.</p>
            </div>
        </div>
    </div>
</div>
