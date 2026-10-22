{{-- «ایجاد قالب پیامکی» for the group page: creates an ordinary (non-OTP) template through the single page's template endpoint. --}}
<div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-labelledby="saveTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="saveTemplateModalLabel"><i class="fa fa-plus-circle me-2 text-success"></i> ایجاد قالب پیامکی</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form id="saveTemplateForm">
                <div class="modal-body">
                    <div id="saveTemplateAlert" class="alert alert-danger py-2 small" style="display: none;"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="saveTemplateTitle">عنوان قالب <span class="text-danger">*</span></label>
                        <input type="text" id="saveTemplateTitle" class="form-control" required maxlength="120" placeholder="مثال: یادآوری پرداخت">
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold" for="saveTemplateContent">متن قالب <span class="text-danger">*</span></label>
                        <textarea id="saveTemplateContent" class="form-control" rows="5" maxlength="2000" required placeholder="مثال: عید مبعث بر شما مبارک باد"></textarea>
                        <div class="form-text">قالب برای پنل پیامکی فعال ساخته می‌شود و بعداً از لیست قالب‌ها قابل انتخاب است. اگر متنی در کادر پیام نوشته باشید، اینجا پر می‌شود.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">ایجاد قالب</button>
                </div>
            </form>
        </div>
    </div>
</div>
