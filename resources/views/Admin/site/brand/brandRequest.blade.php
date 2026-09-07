<!-- brandRequest -->
<div id="brandRequestModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">درخواست ایجاد برند جدید </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <span class="redColor font12">
                    توجه :
                </span><br/>
                <ul class="redColor mb-4 font12">
                    <li>
                        - برندهای ایرانی باید گواهی ثبت علامت تجاری داشته باشند و تصویر آن را باید همراه با درخواست در فرم ارسال فرمایید.
                    </li>
                    <li>
                        - برندهای ایرانی که دارای گواهی ثبت علامت تجاری نیستند ، ثبت نمی شوند.
                    </li>
                    <li>
                        - برای ثبت برند ، اظهار نامه قابل قبول نیست و حتما باید گواهی ثبت برند را ارسال کنید.
                    </li>
                </ul>
                @include('Admin.site.brand.brandForm',['category_id'=>$category_id,'request_by'=>1])
            </div>

        </div>

    </div>
</div>
