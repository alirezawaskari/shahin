{{-- «ذخیره به‌عنوان قالب» for the group page: saves the text being written through the single page's template endpoint. --}}
<div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-labelledby="saveTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="saveTemplateModalLabel"><i class="fa fa-bookmark-o me-2 text-success"></i> ذخیره به‌عنوان قالب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form id="saveTemplateForm">
                <div class="modal-body">
                    <div id="saveTemplateAlert" class="alert alert-danger py-2 small" style="display: none;"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="saveTemplateTitle">عنوان قالب <span class="text-danger">*</span></label>
                        <input type="text" id="saveTemplateTitle" class="form-control" required maxlength="120" placeholder="مثال: یادآوری پرداخت">
                    </div>
                    <div class="small fw-bold mb-1">متن قالب</div>
                    <div id="saveTemplateContent" class="p-2 bg-light border rounded small" style="max-height: 140px; overflow-y: auto; white-space: pre-wrap;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">ذخیره قالب</button>
                </div>
            </form>
        </div>
    </div>
</div>
