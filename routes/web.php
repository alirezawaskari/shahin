<?php

use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContextController;
use App\Http\Controllers\Admin\site\ArticleController;
use App\Http\Controllers\Admin\site\BrandController;
use App\Http\Controllers\Admin\site\CategoryArticleController;
use App\Http\Controllers\Admin\site\ColorController;
use App\Http\Controllers\Admin\site\NewsController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\Artisan;


//Auth Routes
Auth::routes();
Route::get('/pass1word/reset', 'Auth\ResetPasswordController@resetform')->name('reset_pass_form');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('reset_pass');
Route::get('/register/check-user-is-register',[RegisterController::class,'checkUserIsRegister']);
Route::get('/login_by_mobile',[RegisterController::class,'showRegistrationForm'])->name('login_by_mobile');
Route::post('/fastregister', 'Auth\RegisterController@fastregister');
//Route::post('loginRepresentation' , 'LoginController@representation')->name('login.representation');
//Route::post('registerRepresentation' , 'RegisterController@representation')->name('register.representation');

//Reset & Command Routes
Route::group([], function () {

    Route::get('/l/{link}', [\App\Http\Controllers\Admin\ShortLinkController::class,'redirect']);
    Route::get('/factor-item/a-transfer', [AccountingController::class,'transfer']);

    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
    });

    Route::get('/routes', function () {
        $routes =Route::getRoutes();
        $get_method_routes = array();
        foreach ($routes as $item) {
            if ($item->methods()[0] == 'GET'){
                $get_method_routes[]=$item;
            }
        }
         dd((collect($get_method_routes)->pluck('uri'))->toArray());
    });

    Route::get('/clear-config', function () {
        Artisan::call('config:clear');
    });
    Route::get('/clear-route', function () {
        Artisan::call('route:clear');
    });

    Route::get('/clear-view', function () {
        Artisan::call('view:clear');
    });

    Route::get('/get-sms', function () {
        Artisan::call('sms:get');
        return redirect()->back()->with([
            'get_sms' => 'success'
        ]);
    });

    Route::get('/spread-sms', function () {
        Artisan::call('sms:spread');
        return redirect()->back()->with([
            'spread_sms' => 'success'
        ]);
    });

    Route::get('/installmentsms', function () {
        Artisan::call('sms:installment');
        return redirect()->back()->with([
            'installmentsms' => 'success'
        ]);
    });

    Route::get('/exitSuspension', function () {
        Artisan::call('exit:suspension');
        return redirect()->back()->with([
            'exitsuspension' => 'success'
        ]);
    });

    Route::get('/verifyUnverifiedTransaction', function () {
        Artisan::call('verify:unverifiedTransaction');
        return redirect()->back()->with([
            'verifyUnverifiedTransaction' => 'success'
        ]);
    });

    Route::get('/run-query', function () {

        ini_set ('max_execution_time',  -1);

        Artisan::call('command:query');
        return redirect()->back()->with([
            'result' => 'success'
        ]);
    });
});

//Admin Routes
Route::group(['namespace' => 'Admin', 'prefix' => 'dashboard', 'middleware' => ['auth:web', 'checkActive', 'checkAdmin']], function () {
    //dashboard

    Route::get('/run-query', function () {
        Artisan::call('command:query');
        return redirect()->back()->with([
            'run_query' => 'success'
        ]);
    });
    Route::get('/logs' , [AdminController::class,'showLogs'])->name('admin.showLogs')->middleware('SaveLastPage');
    Route::get('/accounting/cash-debtors' , [AccountingController::class,'cashDebtorsIndex'])->name('accounting.cash.index')->middleware('SaveLastPage');
    Route::get('/accounting/filter-cash-debtors' , [AccountingController::class,'filterCashDebtors'])->name('accounting.filter.cash-debtors');
    Route::get('/accounting/installment-debtors' , [AccountingController::class,'installmentDebtorsIndex'])->name('accounting.installment.index')->middleware('SaveLastPage');
    Route::get('/accounting/filter-installment-debtors' , [AccountingController::class,'filterInstallmentDebtors'])->name('accounting.filter.installment-debtors');
    Route::get('/accounting/show/{user}' , [AccountingController::class,'show'])->name('accounting.show')->middleware('SaveLastPage');
    Route::get('/', 'AdminController@dashboard')->middleware('SaveLastPage');
    Route::get('/settings', [ContextController::class,'settings'])->name('settings.index')->middleware('SaveLastPage');
    Route::patch('/settings/update-expire-dates', [ContextController::class,'updateExpireDates'])->name('settings.updateExpireDates');
    Route::post('removeImgWorkSample', 'AdminController@removeImgWorkSample');
    Route::post('/service/fetch-serviceitem-by-service', 'ServiceController@fetchServiceItemsByServiceId');
    Route::get('/dailyDesktop', 'AdminController@dailyDesktop')->middleware('SaveLastPage');
    Route::get('/set-link/{slug}', 'AdminController@setLink');
    Route::get('/filter/{type}', 'AdminController@dashboard');
    Route::post('/cities' , 'AdminController@cities');
    Route::post('/fetch-user' , 'AdminController@user');
    Route::post('/summernote-image' , 'AdminController@summernote_image');
    Route::post('/uploadImage', 'AdminController@uploadImage');
    Route::get('/context/site-rules',[ContextController::class, 'fetchSiteRules'])->name('siteRules.index')->middleware('SaveLastPage');;
    Route::patch('/context/site-rules',[ContextController::class, 'updateSiteRules'])->name('siteRules.update');
    //callcenter
    Route::get('/callcenter/suspension', 'CallcenterController@suspension')->name('callcenter.suspension')->middleware('SaveLastPage');
    Route::get('/callcenter/suspension/filter' , 'CallcenterController@filterSuspension');
    Route::get('/callcenter/paid', 'CallcenterController@paid')->name('callcenter.paid')->middleware('SaveLastPage');
    Route::get('/callcenter/paid/filter' , 'CallcenterController@filterPaid');
    Route::get('/callcenter/desktop', 'CallcenterController@desktop')->name('callcenter.desktop')->middleware('SaveLastPage');
    Route::get('/callcenter/desktop/filter', 'CallcenterController@filterDesktop');
    Route::get('/callcenter/filter' , 'CallcenterController@filter');
    Route::post('/callcenter/registerForm' , 'CallcenterController@registerForm')->name('callcenter.registerForm');
    Route::get('/callcenter/fetchUser' , 'CallcenterController@fetchUser');
    Route::get('/callcenter/deleted' , 'CallcenterController@delete')->name('callcenter.delete')->middleware('SaveLastPage');
    Route::get('/callcenter/restore' , 'CallcenterController@restore')->name('callcenter.restore');
    Route::post('/fastregister', 'CallcenterController@fastregister')->name('fastregister_callcenter');
    Route::post('/fastservice', 'CallcenterController@fastservice')->name('fastservice');
    Route::post('/reason', 'CallcenterController@reason')->name('callcenter.reason');
    Route::post('/callcenter/change', 'CallcenterController@change')->name('callcenter.change');
    Route::get('/tasks-requires' , 'CallcenterController@tasks_requires');
    Route::post('/task-detail' , 'CallcenterController@taskDetail');
    Route::post('/status-form' , 'CallcenterController@statusForm');
    Route::post('/service-form' , 'CallcenterController@service_form');
    Route::post('/service-form-global' , 'CallcenterController@service_form_global');
    Route::post('/transfer-form' , 'CallcenterController@transfer_form');
    Route::post('/task-transfer' , 'CallcenterController@task_transfer');
    Route::post('/callcenter/closeTask' , 'CallcenterController@closeTask');
    Route::resource('callcenter', 'CallcenterController');
    Route::get('short-link/filter', 'ShortLinkController@filter');
    Route::resource('short-link', 'ShortLinkController');
    //factor
    Route::get('/factor/desktop' , 'FactorController@desktop')->name('factor.desktop');
    Route::get('/system-income' , 'FactorController@systemIncome')->name('system.income')->middleware('SaveLastPage');
    Route::get('/factor-desktop' , 'FactorController@filterDesktop')->middleware('SaveLastPage');
    Route::get('/factorexel', 'FactorController@exel')->name('factor.exel');
    Route::get('/factorfilter', 'FactorController@filter')->name('factor.filter');
    Route::post('/factor-comment', 'FactorController@comment')->name('factor.comment');
    Route::post('/factor-manager' , 'FactorController@factor_manager');
    Route::post('/factor/sms', 'FactorController@sms')->name('factor.sms');
    Route::get('/factors' , 'FactorController@filter')->middleware('SaveLastPage');
    Route::get('/factor/deleted' , 'FactorController@deletedList')->name('factor.delete')->middleware('SaveLastPage');
    Route::get('/factor/deleted/filter', 'FactorController@filterDeletedList');
    Route::post('/factor/restore', 'FactorController@restore');
    Route::resource('/factor', 'FactorController');
    Route::resource('/category-permission', 'CategoryPermissionController');
    Route::post('/factor-update' , 'FactorController@update');
    //check
    Route::get('/check/filter' , 'CheckController@filter');
    Route::post('/check/update-status' , 'CheckController@updateStatus');
    Route::resource('/check', 'CheckController');
    Route::post('/check/update/{check}', 'CheckController@update')->name('check.update');
    //total factor item
    Route::post('/totalFactorItem/suspension', 'TotalFactorItemController@suspension')->name('totalFactorItem.suspension');
    Route::get('/totalFactorItem/desktop', 'TotalFactorItemController@desktop')->name('totalFactorItem.desktop')->middleware('SaveLastPage');
    Route::get('/totalFactorItem/desktop/filter' , 'TotalFactorItemController@filterDesktop');
    Route::post('/totalFactorItem/request-contract-settlement', 'TotalFactorItemController@requestContractSettlement')->name('totalFactorItem.requestContractSettlement');
    Route::post('/totalFactorItem/contract-settlement', 'TotalFactorItemController@contractSettlement')->name('totalFactorItem.contractSettlement');
    Route::get('/totalFactorItem/{totalFactorItem}/installment', 'TotalFactorItemController@installments')->middleware('SaveLastPage');
    Route::get('/totalFactorItem/checks/{totalFactorItem}', 'TotalFactorItemController@checks')->name('totalFactorItem.checks')->middleware('SaveLastPage');
    Route::post('/totalFactorItem/upload-document', 'TotalFactorItemController@uploadDocument')->name('totalFactorItem.uploadDocument');
    Route::post('/totalFactorItem/save-extra-pay', 'TotalFactorItemController@saveExtraPay')->name('totalFactorItem.saveExtraPay');
    Route::post('/totalFactorItem/store-impunity', 'TotalFactorItemController@storeImpunity')->name('totalFactorItem.storeImpunity');
    Route::delete('/totalFactorItem/destroy-impunity/{impunity}', 'TotalFactorItemController@destroyImpunity')->name('totalFactorItem.destroyImpunity');
    Route::get('/totalFactorItem/reports', 'TotalFactorItemController@reports')->middleware('SaveLastPage');
    Route::get('/totalFactorItem/reports/filter' , 'TotalFactorItemController@filterReport');
    Route::get('/totalFactorItem/reports/desktop', 'TotalFactorItemController@desktopReports')->middleware('SaveLastPage');
    Route::get('/totalFactorItem/reports/desktop/filter' , 'TotalFactorItemController@filterDesktopReport');
    Route::get('/totalFactorItem/filter', 'TotalFactorItemController@filter')->name('totalFactorItem.filter');
    Route::post('/totalFactorItem/sendInitialLink', 'TotalFactorItemController@sendInitialLink')->name('totalFactorItem.sendInitialLink');
    Route::post('/totalFactorItem/change-status', 'TotalFactorItemController@changeStatus');
    Route::post('/totalFactorItem/change-status-mali', 'TotalFactorItemController@changeStatusMaliFactor');
    Route::post('/totalFactorItem/payment', 'TotalFactorItemController@payment')->name('totalFactorItem.sendPayment');
    Route::post('/totalFactorItem/save-installments-paid', 'TotalFactorItemController@saveInstallmentsPaid')->name('totalFactorItem.saveInstallment');
    Route::post('/totalFactorItem/return-payment', 'TotalFactorItemController@returnPayment')->name('totalFactorItem.returnPayment');
    Route::get('/totalFactorItem/calculate', 'TotalFactorItemInstallmentController@showCalculate')->middleware('SaveLastPage');
    Route::post('/totalFactorItem/calculate', 'TotalFactorItemController@updateCalculate')->name('totalFactorItem.calculate');
    Route::post('/totalFactorItem/close', 'TotalFactorItemController@close');
    Route::resource('totalFactorItem', 'TotalFactorItemController');
    Route::get('/totalFactorItemInstallment/filter', 'TotalFactorItemInstallmentController@filter')->name('totalFactorItemInstallment.filter');
    Route::resource('totalFactorItemInstallment', 'TotalFactorItemInstallmentController');
    //total factor item contract settlement
    Route::post('/totalFactorItemContractSettlement', 'TotalFactorItemContractSettlementController@confirm')->name('totalFactorItemContractSettlement.confirm');
    //financial plans types
    Route::post('/financialPlansType/selectByCategory', 'CategoryController@child')->name('category.get.child');
    Route::post('/financialPlansType/setByItem', 'FinancialPlansTypeController@setFinancialPlansTypeByItem');
    Route::post('/financialPlansType/setByPaymentType', 'FinancialPlansTypeController@setFinancialPlansTypeByPaymentType');

    Route::resource('financialPlansType' , 'FinancialPlansTypeController');
    //contract form
    Route::resource('contractForm', 'ContractFormController');
    Route::get('/contractForm/items/{contract_form}', 'ContractFormController@formItems')->name('contract.formItems')->middleware('SaveLastPage');
    Route::get('/contract_before_form_item_create/{contract_form}', 'contractFormController@formItemCreate')->name('contract.before.formItemCreate')->middleware('SaveLastPage');
    //factor item
    Route::get('/factorItem/{factorItem}/fastEdit', 'FactorItemController@fastEdit')->middleware('SaveLastPage');
    Route::post('/factorItem/close', 'FactorItemController@close');
    Route::get('/factorItem/desktop', 'FactorItemController@desktop')->name('factorItem.desktop')->middleware('SaveLastPage');
    Route::get('/factorItem/desktop/filter' , 'FactorItemController@filterDesktop');
    Route::get('/factorItem/filter' , 'FactorItemController@filter');
    Route::post('/factorItem/update-information', 'FactorItemController@updateInformation')->name('factorItem.information');
    Route::post('/factorItem/create-factor', 'FactorItemController@createFactor')->name('factorItem.createFactor');
    Route::post('/factorItem/update-calculate', 'FactorItemController@updateCalculate')->name('factorItem.calculate');
    Route::post('/factorItem/payment', 'FactorItemController@payment')->name('factorItem.sendPayment');
    Route::post('/factorItem/return-payment', 'FactorItemController@returnPayment')->name('factorItem.returnPayment');
    Route::post('/factorItem/upload-document', 'FactorItemController@uploadDocument')->name('factorItem.document');
    Route::post('/factorItem/completion-file-work', 'FactorItemController@completionFileWork')->name('factorItem.completion');
    Route::get('/previous-step/{id}', 'FactorItemController@previousStep')->middleware('SaveLastPage');
    Route::get('/next-step/{id}', 'FactorItemController@nextStep')->middleware('SaveLastPage');
    Route::post('/save-image', 'FactorItemController@saveImage');
    Route::get('/factorItem/delete-service-tariff/{id}' , 'FactorItemController@deleteServiceTariff')->name('factorItem.deleteServiceTariff')->middleware('SaveLastPage');
    Route::post('/factorItem/update-product' , 'FactorItemController@updateProduct')->name('factorItem.updateProduct');
    Route::post('/factorItem/update-service-tariff' , 'FactorItemController@updateServiceTariff')->name('factorItem.updateServiceTariff');
    Route::get('/factorItem/expire', 'FactorItemController@expire')->middleware('SaveLastPage');
    Route::post('/factorItem/sendInitialLink', 'FactorItemController@sendInitialLink')->name('factorItem.sendInitialLink');
    Route::post('/factorItem/change-statusFactor', 'FactorItemController@changeStatusFactor');
    Route::resource('factorItem', 'FactorItemController');
    Route::post('/factoritem/turn-ratings' , 'FactorItemController@turnRatings')->name('factorItem.turnRatings');
    Route::post('/factoritem/set-center' , 'FactorItemController@setCenter')->name('factorItem.setCenter');
    Route::post('/factoritem/change-factoritemable' , 'FactorItemController@changeFactoritemable')->name('factorItem.changeFactoritemable');
    Route::post('/factoritem/suspension', 'FactorItemController@suspension')->name('factorItem.suspension');
    Route::get('/factorItemProduct/filter' , 'FactorItemProductController@filter');
    Route::post('/factorItem/change-financial-plans-type/{factorItem}' , 'FactorItemController@changeFinancialPlansType')->name('factorItem.changeFinancialPlansType');

    Route::resource('factorItemProduct', 'FactorItemProductController');
    Route::resource('factorItemInstallment', 'FactorItemInstallmentController');
    Route::resource('factorItemServiceTariff', 'FactorItemServiceTariffController');

    //purchase invoice
    Route::get('/purchaseInvoice/desktop', 'PurchaseInvoiceController@desktop')->name('purchaseInvoice.desktop')->middleware('SaveLastPage');
    Route::get('/purchaseInvoice/desktop/filter', 'PurchaseInvoiceController@filterDesktop');
    Route::get('/purchaseInvoice/filter', 'PurchaseInvoiceController@filter');
    Route::post('/purchaseInvoice/save-report/{purchaseInvoice}', 'PurchaseInvoiceController@saveReport')->name('purchaseInvoice.report');
    Route::post('/purchaseInvoice/save-payment/{purchaseInvoice}', 'PurchaseInvoiceController@savePayment')->name('purchaseInvoice.savePayment');
    Route::post('/purchaseInvoice/return-payment/{purchaseInvoice}', 'PurchaseInvoiceController@returnPayment')->name('purchaseInvoice.returnPayment');
    Route::post('/purchaseInvoice/confirm/{purchaseInvoice}', 'PurchaseInvoiceController@confirm')->name('purchaseInvoice.confirm');
    Route::get('/purchaseInvoice/reject/{purchaseInvoice}', 'PurchaseInvoiceController@reject')->name('purchaseInvoice.reject')->middleware('SaveLastPage');
    Route::post('/purchaseInvoice/change-status/{purchaseInvoice}', 'PurchaseInvoiceController@changeStatus')->name('purchaseInvoice.changeStatus');
    Route::post('/purchaseInvoice/send-product/{purchaseInvoice}', 'PurchaseInvoiceController@sendProduct')->name('purchaseInvoice.sendProduct');
    Route::get('/create-product-purchaseInvoice/{purchaseInvoice}', 'PurchaseInvoiceController@createProduct')->middleware('SaveLastPage');
    Route::post('/store-product/{purchaseInvoice}', 'PurchaseInvoiceController@storeProduct')->name('purchaseInvoice.product');
    Route::post('/purchaseInvoice/save-report/{purchaseInvoice}', 'PurchaseInvoiceController@saveReport')->name('purchaseInvoice.report');
    Route::get('/purchase-invoice-installment-delete/{purchaseInvoiceInstallment}', 'PurchaseInvoiceInstallmentController@destroy')->middleware('SaveLastPage');
    Route::resource('purchaseInvoice', 'PurchaseInvoiceController');
    Route::resource('supplier', 'SupplierController');
    //employee
    Route::get('/employee/filter', 'EmployeeController@filter')->name('employee.filter');
    Route::get('/seller/filter', 'EmployeeController@sellerFilter')->name('seller.filter');
    Route::get('/employee-financial-report/{employee}' , 'EmployeeController@financialReport')->name('employee.financial.report')->middleware('SaveLastPage');
    Route::get('/employee-wallet/{user}/{key}' , 'EmployeeController@walletTransaction')->name('employee.wallet')->middleware('SaveLastPage');
    Route::get('/employee-performance-detail/{user}' , 'EmployeeController@performanceDetail')->name('employee.performanceDetail')->middleware('SaveLastPage');
    Route::get('/employee-performance-detail-filter/{user}' , 'EmployeeController@performanceDetailFilter');
    Route::get('/employee-wallet-filter/{user}/{key}' , 'EmployeeController@walletTransactionFilter');
    Route::resource('employee' , 'EmployeeController');
    //user
    Route::get('/admin-user' , 'UserController@admin')->name('user.admin');
    Route::get('/user-deleted' , 'UserController@delete')->name('user.delete');
    Route::get('/user-restore' , 'UserController@restore')->name('user.restore');
    Route::get('/user/user-histories/{user}' , 'UserController@fetchUserHistories')->name('user.histories');
    Route::get('/userfilter', 'UserController@filter')->name('user.filter');
    Route::post('/user/sendPassword' , 'UserController@sendPassword');
    Route::get('/user/creditDetail/{user}' , 'UserController@creditDetail')->middleware('SaveLastPage');
    Route::get('/user/login/{user}' , 'UserController@login');
    Route::resource('user', 'UserController');
    Route::post('/city/select', 'UserController@cityselect')->name('city.select');
    Route::post('/user/select', 'UserController@userselect')->name('user.select');
    Route::get('/users' , 'UserController@filter');
    Route::get('/sellers' , 'EmployeeController@sellers')->name('sellers')->middleware('SaveLastPage');
    Route::post('/user-delete' , 'UserController@user_delete');
    Route::post('/user-delete-check' , 'UserController@user_delete_check');
    Route::post('/user-manager' , 'UserController@user_manager');
    //userCreditDetail
    Route::resource('userCreditDetail' , 'UserCreditDetailController');
    Route::post('/userCreditDeetailStore/{user}' , 'UserCreditDetailController@store')->name('userCreditDetail.store');
    //role
    Route::get('/role-create' , 'RoleController@create')->name('role.create')->middleware('SaveLastPage');
    Route::get('/role/get-user-by-role' , 'RoleController@getUserByRole')->name('role.getUser')->middleware('SaveLastPage');
    Route::resource('role', 'RoleController');
    Route::resource('permission', 'PermissionController');
    //ticket
    Route::get('/ticket/send', 'TicketController@sendTicket')->name('send.tickets')->middleware('SaveLastPage');
    Route::post('/ticket/refer', 'TicketController@refer')->name('ticket.refer');
    Route::get('/ticket/received', 'TicketController@receivedTicket')->name('received.tickets')->middleware('SaveLastPage');
    Route::get('/ticket/desktop', 'TicketController@desktop')->name('desktop.tickets')->middleware('SaveLastPage');
    Route::get('/ticket/send/filter', 'TicketController@filterSend');
    Route::get('/ticket/received/filter', 'TicketController@filterReceived');
    Route::get('/ticket/desktop/filter', 'TicketController@filterDesktop');
    Route::resource('ticket', 'TicketController');
    Route::resource('ticketAnswer', 'TicketAnswerController');
    //service & category
    Route::post('/service/selectByCategory', 'ServiceController@selectByCategory');
    Route::get('/service/serviceItem/create/{service}', 'ServiceItemController@createByService')->name('service.serviceItem.create');
    Route::get('/service/serviceItem/{service}', 'ServiceItemController@serviceItemList')->name('service.serviceItem.all')->middleware('SaveLastPage');
    Route::resource('service', 'ServiceController');
    Route::resource('serviceItem', 'ServiceItemController');

    Route::group(['namespace' => 'site', 'prefix' => 'site'], function () {
        Route::resource('/category', 'CategoryController');
        Route::get('/category/sub/{cat_id}', 'CategoryController@subCategory')->name('category.subCategory')->middleware('SaveLastPage');
        Route::post('/findChild', 'CategoryController@findChild');
        Route::post('/changePosition', 'CategoryController@changePosition');
        Route::get('/categoryPosition/{id}', 'CategoryController@categoryPosition')->name('categoryPosition')->middleware('SaveLastPage');
        Route::post('/removeCatMenu', 'CategoryController@removeCatMenu');
        Route::post('/category/fetch-services-by-category', 'CategoryController@fetchServicesByCategory');


        Route::resource('/attributeGroup', 'AttributeGroupController');
        Route::get('/createByCategory/{cat_id}', 'AttributeGroupController@createByCategory')->name('attributeGroup.createByCategory')->middleware('SaveLastPage');

        Route::resource('/attribute', 'AttributeController');
        Route::get('/attribute/create/{id}', 'AttributeController@createAttribute')->name('createAttribute')->middleware('SaveLastPage');
        Route::resource('/attributeItem', 'AttributeItemController');
        Route::get('/attributeItem/create/{id}', 'AttributeItemController@createAttributeItem')->name('createAttributeItem')->middleware('SaveLastPage');
        Route::post('/addRequestAttributeItem', 'AttributeItemController@addRequestAttributeItem');
        Route::get('/attributeRequest/suggest', 'AttributeItemController@suggest')->name('attributeRequest.suggest')->middleware('SaveLastPage');

        //brand
        Route::get('/brand/filter', [BrandController::class,'filter'])->name('brand.filter');
        Route::resource('/brand', 'BrandController');
        Route::get('/createByCatId/{id}', 'BrandController@createByCatId')->name('brand.createByCatId')->middleware('SaveLastPage');
        Route::get('/statusBrand/{brand}', 'BrandController@statusBrand')->name('statusBrand')->middleware('SaveLastPage');
        Route::post('/findBrand', 'CategoryController@findBrand');

        Route::get('/color/filter', [ColorController::class,'filter'])->name('color.filter');
        Route::resource('/color', 'ColorController');
        Route::post('/searchCategory', 'CategoryController@searchCategory');

        Route::resource('/slider', 'SliderController');
        Route::get('/slider-old', 'SliderController@indexOldSite');//sliders of old site
        Route::get('/slider-mobile-old', 'SliderController@indexMobileOldSite');//mobile sliders of old site
        Route::get('/slider-mobile-old-create', 'SliderController@createMobileOld')->name('slider.mobile.old.create');//sliders of old mobile crate
        Route::post('/store-slider-mobile-old', 'SliderController@storeOldSliderMobile')->name('slider.mobile.old.store');//store sliders of old mobile site
        //remove image slider of category in edit
        Route::post('/removeImgSlider', 'SliderController@removeImgSlider');

        Route::get('/categoryArticle/filter', [CategoryArticleController::class,'filter'])->name('categoryArticle.filter');
        Route::resource('/categoryArticle', 'CategoryArticleController');
        Route::get('/article/filter', [ArticleController::class,'filter'])->name('article.filter');
        Route::resource('/article', 'ArticleController');

        Route::get('/news/filter', [NewsController::class,'filter'])->name('news.filter');
        Route::resource('/news', 'NewsController');
        Route::resource('/categoryNews', 'CategoryNewsController');

        Route::resource('/validation','ValidationController');
//      Route::post('/getValidation','ProductValidationController@getValidation');

        Route::resource('/comment', 'CommentController');
        Route::get('/comments/desktop', 'CommentController@desktop')->middleware('SaveLastPage');

        Route::resource('/coupon', 'CouponController');
        Route::resource('/couponItem', 'CouponItemsController');
        Route::post('/coupon/statusCoupon', 'CouponController@statusCoupon');
        Route::get('/coupons/discountVarietyProduct', 'CouponController@discountVarietyProduct')->name('coupons.discountVarietyProduct')->middleware('SaveLastPage');
        Route::get('/createByCouponId/{id}', 'CouponItemsController@createByCouponId')->name('couponItem.createByCouponId')->middleware('SaveLastPage');
        Route::post('/findCategoryByProductName', 'CouponItemsController@findCategoryByProductName');
        Route::post('/findServiceByCategoryId', 'CouponItemsController@findServiceByCategoryId');
        Route::post('/findServiceItem', 'CouponItemsController@findServiceItem');
        Route::post('/productAttributeInCoupon', 'CouponItemsController@productAttribute');

//        Route::resource('/periodicPrices','PeriodicPriceController');
//        Route::post('/periodicPrices/storeProductIds','PeriodicPriceController@storeProductIds');
//        Route::post('/periodicPrices/centerProductsSearch','PeriodicPriceController@centerProductsSearch');
//        Route::post('/periodicPrices/centerProductsOrderBy','PeriodicPriceController@centerProductsOrderBy');
//        Route::post('/periodicPrices/removeProductAttribute','PeriodicPriceController@removeProductAttribute');
//        Route::post('/periodicPrices/checkDate','PeriodicPriceController@checkDate');
//        Route::get('/periodicPricesProduct/completeSelection','PeriodicPriceController@completeSelection');
//        Route::post('/periodicPrices/deleteProductAttribute','PeriodicPriceController@deleteProductAttribute');

    });

    Route::resource('/about', 'AboutController');
    Route::get('/userInvite', 'UserInviteController@index')->name('userInvite.index')->middleware('SaveLastPage');
    Route::resource('/inviteCategory', 'InviteCategoryController');
    Route::post('/searchTitleProduct', 'InviteCategoryController@searchTitleProduct');

    Route::get('/service_before_form/{service}', 'ServiceController@beforeForm')->name('service.before.form')->middleware('SaveLastPage');
    Route::get('/service_before_form_create/{service}', 'ServiceController@beforeFormCreate')->name('service.before.form.create')->middleware('SaveLastPage');
    Route::get('/service_before_form_item/{beforeform}', 'ServiceController@beforeFormItem')->name('service.before.form.item')->middleware('SaveLastPage');
    Route::get('/service_before_form_item_create/{beforeform}', 'ServiceController@beforeFormItemCreate')->name('service.before.form.item.create')->middleware('SaveLastPage');
    Route::post('/service/form', 'ServiceController@form');
    Route::post('/service/fetchForm', 'ServiceController@fetchForm');
    Route::resource('before_form', 'BeforeFormController')->except(['index', 'create', 'show']);
    Route::resource('before_form_item', 'BeforeFormItemController')->except(['index', 'create', 'show']);
    Route::resource('serviceItem', 'ServiceItemController');

    //installment
    Route::resource('/installment', 'InstallmentController');
    Route::get('/createInstallmentCategory/{id}', 'InstallmentController@createCategory')->middleware('SaveLastPage');
    Route::get('/createInstallmentService/{id}', 'InstallmentController@createService')->middleware('SaveLastPage');
    Route::get('/installment/service/{id}', 'InstallmentController@serviceInstallment')->middleware('SaveLastPage');
    Route::get('/installment/detail/service/{id}', 'InstallmentController@serviceInstallmentDetail')->name('installment.detail.service')->middleware('SaveLastPage');
    Route::post('/installment/finish', 'InstallmentController@finishInstallment')->name('finishInstallment');

    //products
    Route::resource('product', 'ProductController');
    Route::post('/removeImgGallery', 'ProductController@removeImgGallery');
    Route::post('/product/store', 'ProductController@store');
    Route::post('/removeImageCreated', 'ProductController@removeImageCreated');
    Route::get('/find/product', 'ProductController@findProduct')->name('findProduct');

    Route::post('/searchTransferProduct', 'ProductController@searchTransferProduct');
    Route::post('/selectTransferProduct', 'ProductController@selectTransferProduct');
    Route::post('/centerSellToo', 'ProductController@centerSellToo');

    //service tariffs
    Route::post('/serviceTariff/selectByTariff', 'ServiceTariffController@selectByTariff');
    Route::post('/serviceTariff/getSessionNumber', 'ServiceTariffController@getSessionNumber');
    Route::get('/serviceTariff/filter', 'ServiceTariffController@filter')->name('serviceTariff.filter');
    Route::resource('serviceTariff', 'ServiceTariffController');

    //storeroom
    Route::resource('storeRoom', 'StoreRoomController');
    Route::get('/subWarehouses/{id}', 'StoreRoomController@subWarehouses')->name('subWarehouses')->middleware('SaveLastPage');
    Route::get('/storeRoomGood/{id}', 'StoreRoomController@storeRoomGood')->name('storeRoomGood')->middleware('SaveLastPage');
    Route::get('/editStoreRoomGood/{id}/{product_id}', 'StoreRoomController@editStoreRoomGood')->middleware('SaveLastPage');
    Route::get('/storeRoomMaterial/{id}', 'StoreRoomController@storeRoomMaterial')->name('storeRoomMaterial')->middleware('SaveLastPage');
    Route::get('/editStoreRoomMaterial/{id}/{product_id}', 'StoreRoomController@editStoreRoomMaterial')->middleware('SaveLastPage');
    Route::delete('/productStoreRoom/{id}', 'StoreRoomController@destroyProductStoreRoom')->name('productStoreRoom.destroy');
    Route::patch('/productStoreRoomUpdate/{id}', 'StoreRoomController@productStoreRoomUpdate')->name('productStoreRoomUpdate');

    //show store Room Information in modal and edit it in seller modal
    Route::get('/getProductStoreRoom/{product_id}/{id}','StoreRoomController@productStoreRoomByProductId')->name('getProductStoreRoom')->middleware('SaveLastPage');
    Route::patch('/productStoreRoom/{id}', 'StoreRoomController@updatePivotProductStoreRoom')->name('updatePivotProductStoreRoom');


    Route::post('/removeReview', 'ProductController@removeReview');
    Route::resource('countingUnit', 'CountingUnitController');
    //request storeroom
    Route::resource('request', 'RequestController');
    Route::post('/productByStoreRoom', 'RequestController@productByStoreRoom');
    Route::get('/changeStatusRequest/{id}/{status}', 'RequestController@changeStatusRequest');
    Route::post('/rejectedStatusRequest', 'RequestController@rejectedStatusRequest');
    Route::post('/stockProduct', 'RequestController@stockProduct');
    Route::resource('changeStoreRoom', 'ChangeStoreRoomController');

    Route::resource('/productValidation','ProductValidationController');
    Route::resource('/validation','ValidationController');
    Route::post('/getValidation','ProductValidationController@getValidation');
    Route::post('/removeImageProduct','ProductController@removeImageProduct');
    Route::post('/saveChangeVisitProduct','ProductController@saveChangeVisitProduct');

    //organization & representation
    Route::get('/organization/createUser', 'OrganizationController@createUser')->name('organization.user.create')->middleware('SaveLastPage');
    Route::post('/organization/storeUser', 'OrganizationController@storeUser')->name('organization.user.store');
    Route::get('/organization/filter', 'OrganizationController@filter')->name('organization.filter');
    Route::get('/organization/items', 'OrganizationController@items')->name('organization.items')->middleware('SaveLastPage');
    Route::resource('organization', 'OrganizationController');
    Route::resource('representation', 'RepresentationController');
    //center
    Route::get('/center-service/{center}' , 'CenterController@service')->name('center.service')->middleware('SaveLastPage');
    Route::get('/center/filter', 'CenterController@filter')->name('center.filter');
    Route::get('/center/{center}/serviceTariff', 'CenterController@serviceTariffs')->name('center.serviceTariff')->middleware('SaveLastPage');
    Route::get('/center/{center}/serviceTariff/create', 'CenterController@createServiceTariff')->middleware('SaveLastPage');
    Route::get('/doctorsByCenter/{center_id}', 'CenterController@doctorsByCenter')->name('doctorsByCenter')->middleware('SaveLastPage');
    Route::get('/productsByCenterId/{id}', 'CenterController@productsByCenterId')->name('productsByCenterId')->middleware('SaveLastPage');
    Route::get('/center/products/{id}/{center_id}/edit', 'CenterController@productEdit')->name('center.products.edit')->middleware('SaveLastPage');
    Route::patch('/center/products/update/{product}', 'CenterController@productUpdate')->name('center.products.update');
    Route::get('/getCenterCategory', 'CenterController@getCenterCategory')->middleware('SaveLastPage');
    Route::resource('center', 'CenterController');

    //center request
    Route::get('/centerRequest/filter', 'CenterRequestController@filter')->name('centerRequest.filter');
    Route::resource('centerRequest', 'CenterRequestController');

    //fullCalendar
    Route::resource('/fullCalendar', 'CalendarController');
    Route::get('/allCalenderEvent', 'CalendarController@allCalenderEvent')->middleware('SaveLastPage');
    Route::get('/fullCalendarDoctor/{user_id}', 'CalendarController@fullCalendarDoctor')->name('fullCalendarDoctor')->middleware('SaveLastPage');

    Route::get('/list-product-storeRoom/{storeRoom}' , 'StoreRoomController@listProduct')->name('storeRoom.list.product')->middleware('SaveLastPage');
    Route::get('/filter-product-storeRoom/{storeRoom}', 'StoreRoomController@filter')->name('storeRoom.product.filter');

    Route::post('/ckeditor/upload', 'CkeditorController@upload')->name('ckeditor.upload');

    Route::resource('/products', 'ProductController');
    Route::get('/products/visit/{id}', 'ProductController@visit')->middleware('SaveLastPage');
    Route::get('/products/{id}/{status_id}/status', 'ProductController@status')->middleware('SaveLastPage');
    Route::post('/assignSimilarProduct','ProductController@assignSimilarProduct');
    Route::post('/getProduct','ProductController@getProduct');

    Route::get('/find/product', 'ProductController@find');

    Route::get('/manage/product/createGood', 'ProductController@create')->name('createGood')->middleware('SaveLastPage');
    Route::get('/manage/product/createGood/{type}/{id}', 'ProductController@createByValues')->name('createGoodByValues')->middleware('SaveLastPage');

    Route::get('/manage/product/createMaterials', 'ProductController@createMaterials')->name('createMaterials')->middleware('SaveLastPage');
    Route::get('/manage/product/createMaterials/{type}/{id}', 'ProductController@createMaterialsByValues')->name('createMaterialsByValues')->middleware('SaveLastPage');

    Route::post('/product/storeMaterials', 'ProductController@storeMaterials')->name('products.storeMaterials');

    Route::resource('/product/price', 'PriceProductController');
    Route::post('/product/price/storeWithoutAttr', 'PriceProductController@storeWithoutAttr');

    Route::get('/product/pricesList/{id}', 'PriceProductController@pricesList')->middleware('SaveLastPage');
    Route::get('/product/pricesListWithoutAttribute/{id}', 'PriceProductController@pricesListWithoutAttribute')->middleware('SaveLastPage');
    Route::post('/product/updateProductAttribute', 'PriceProductController@updateProductAttribute');
    Route::post('/product/returnProductAttribute', 'PriceProductController@returnProductAttribute');

    Route::post('/product/searchProductTitle', 'ProductController@searchProductTitle');
    Route::get('/product/editInformation/{id}', 'ProductController@editInformation')->name('editInformation');

    Route::post('/saveChangeVisitProduct','ProductController@saveChangeVisitProduct');

    Route::resource('/periodicPrices','PeriodicPriceController');
    Route::post('/periodicPrices/storeProductIds','PeriodicPriceController@storeProductIds');

    Route::post('/periodicPrices/removeProductAttribute','PeriodicPriceController@removeProductAttribute');
    Route::post('/periodicPrices/checkDate','PeriodicPriceController@checkDate');
    Route::get('/periodicPricesProduct/completeSelection','PeriodicPriceController@completeSelection')->middleware('SaveLastPage');
    Route::post('/periodicPrices/deleteProductAttribute','PeriodicPriceController@deleteProductAttribute');

//    Route::resource('/centerProducts', 'CenterProductController');
//    Route::get('/centerProducts/{product_id}/{id}', 'CenterProductController@centerProducts');

    Route::resource('/pack', 'PackController');
    Route::post('/getProductsPack', 'PackController@getProductsPack');

    //session
    Route::get('/session/filter' , 'SessionController@filter');
    Route::get('/session/desktop-confirm' , 'SessionController@desktopConfirm')->name('session.desktop.confirm')->middleware('SaveLastPage');
    Route::get('/session/desktop-confirm/filter' , 'SessionController@filterDesktopConfirm');
    Route::get('/session/desktop-complete' , 'SessionController@desktopComplete')->name('session.desktop.complete')->middleware('SaveLastPage');
    Route::get('/session/desktop-complete/filter' , 'SessionController@filterDesktopComplete');
    Route::get('/session/confirm/{session}' , 'SessionController@confirm')->name('session.confirm')->middleware('SaveLastPage');
    Route::post('/session/update' , 'SessionController@update');
    Route::get("/session/user-start-entry/{session}" , 'SessionController@userSessionStartEntry')->name('user.session.start_entry')->middleware('SaveLastPage');
    Route::get("/session/user-end-entry/{session}" , 'SessionController@userSessionEndEntry')->name('user.session.end_entry')->middleware('SaveLastPage');
    Route::resource('session', 'SessionController');
    Route::get('/session-reject/{id}' , 'SessionController@rejectSession')->name('session.reject')->middleware('SaveLastPage');
    //wallet transation
    Route::resource('WalletTransaction', 'WalletTransactionController');
    Route::get('/wallet-update/{wallet}', 'WalletTransactionController@wallet_update')->name('wallet.update')->middleware('SaveLastPage');
    //resume
    Route::get('/resume/filter' , 'ResumeController@filter');
    Route::get('/resume/desktop' , 'ResumeController@desktop')->middleware('SaveLastPage');
    Route::get('/resume/desktop/filter' , 'ResumeController@filterDesktop');
    Route::post('/resume-delete' , 'ResumeController@delete');
    Route::resource('recruitmentNotice', 'RecruitmentNoticeController');
    Route::resource('resume', 'ResumeController');
    //agency
    Route::get('/agency/filter', 'AgencyController@filter');
    Route::get('/agency-suspend', 'AgencyController@suspend')->name('agency.suspend')->middleware('SaveLastPage');
    Route::resource('agency', 'AgencyController');
    Route::resource('agencyType', 'AgencyTypeController');
    //contract
    Route::resource('contract', 'ContractController');
    Route::resource('arrangeContract', 'ArrangeContractController');
    Route::get('/arrangeContract/{id}/{status}', 'ArrangeContractController@status');
    Route::get('/arrangeContractList/{contract_id}', 'ArrangeContractController@arrangeContractList')->middleware('SaveLastPage');
    //sms
    Route::get('/sms/single/create', 'GroupsmsController@single')->name('sms.single')->middleware('SaveLastPage');
    Route::get('/sms/single/edit/{groupsms}', 'GroupsmsController@singleEdit')->name('sms.edit')->middleware('SaveLastPage');
    Route::post('/sms/single/store', 'GroupsmsController@singleStore')->name('sms.single.store');
    Route::post('/sms/sendConsent', 'GroupsmsController@sendConsent')->name('sms.sendConsent');
    Route::post('/groupsms/phone/store', 'GroupsmsController@groupsmsPhone')->name('groupsms.phone.store');
    Route::patch('/groupsms/phone/update/{groupsms}', 'GroupsmsController@groupsmsPhoneUpdate')->name('groupsms.phone.update');
    Route::resource('groupsms', 'GroupsmsController');
    //safe box
    Route::resource('safeBox', 'SafeBoxController');
    //phone & newsletter
    Route::resource('phone', 'PhoneController');
    Route::get('/phonefilter', 'PhoneController@filter')->name('phone.filter');
    Route::resource('phonelist', 'PhoneListController');
    Route::get('/phoneExel', 'PhoneController@exel')->name('phone.exel');
    Route::resource('newsletter', 'NewsletterController');
    Route::get('/newsletterFactor', 'NewsletterController@exel')->name('newsletter.exel');
    //contact
    Route::get('/contact/filter', 'ContactController@filter');
    Route::get('/contact/desktop/filter', 'ContactController@filterDsktop');
    Route::get('/contact/desktop', 'ContactController@desktop')->name('contact.desktop')->middleware('SaveLastPage');
    Route::resource('contact', 'ContactController');

    //gallery
    Route::resource('gallerycategory', 'GalleryCategoryController');
    Route::get('/gallery/destroy/{gallery}', 'GalleryController@destroy')->name('gallery.destroy');
    //content site
    Route::resource('about', 'AboutController');
    Route::resource('help', 'HelpController');
    Route::resource('setting', 'SettingController');
    //Route::resource('coupon', 'CouponController');

    Route::resource('lalecard', 'LalecardController');
    Route::resource('province', 'ProvinceController');
    Route::resource('city', 'CityController');
//    Route::resource('area', 'AreaController');
});

//Front Routes
Route::get('/', 'HomeController@index')->name('index');
Route::get('/help', 'HelpController@index')->name('site.help')->middleware('SaveLastPage');
Route::get('/calculate', 'FactorItemInstallmentController@index')->name('site.help')->middleware('SaveLastPage');
Route::get('/link/{callcenter}', 'HomeController@index');
Route::get('/message', 'HomeController@message')->middleware('SaveLastPage');
Route::get('/mobile', 'HomeController@mobile')->name('mobile');
Route::get('/mobile/link/{callcenter}', 'HomeController@mobile')->middleware('SaveLastPage');
Route::post('/newsletter', 'HomeController@newsletter')->name('newsletter');
Route::post('/phone', 'HomeController@phone')->name('phone');
Route::post('/service/financialPlansType', 'ServiceController@getFinancialPlansType');
Route::get('/services', 'ServiceController@index')->name('services')->middleware('SaveLastPage');
Route::get('/services/{service}', 'ServiceController@single')->middleware('SaveLastPage');
Route::get('/services/{service}/{callcenter}', 'ServiceController@single')->middleware('SaveLastPage');
Route::get('/services/{service}/{callcenter}/{task}', 'ServiceController@single')->middleware('SaveLastPage');
Route::get('/services/{service}/{callcenter}/{task}/{factor}', 'ServiceController@single')->middleware('SaveLastPage');
Route::get('/recruitment-notice', 'RecruitmentNoticeController@index')->middleware('SaveLastPage');
Route::get('/Permanent-employment', 'RecruitmentNoticeController@index')->middleware('SaveLastPage');
Route::get('/challenge', 'HomeController@challenge')->middleware('SaveLastPage');
Route::get('/contact', 'ContactController@index')->middleware('SaveLastPage');
Route::post('/send_resume', 'RecruitmentNoticeController@send_resume');
Route::post('/savecontact', 'ContactController@savecontact');
Route::get('/about', 'AboutController@index')->middleware('SaveLastPage');
Route::get('/partner', 'PartnerController@partner')->middleware('SaveLastPage');
Route::get('/agency', 'AgencyController@index')->name('agency')->middleware('SaveLastPage');
Route::post('/agency', 'AgencyController@store');
Route::get('/centerRequest/create', 'CenterRequestController@create')->middleware('SaveLastPage');
Route::post('/centerRequest/store', 'CenterRequestController@store');
Route::get('/agencies', 'AgencyController@index')->name('agencies')->middleware('SaveLastPage');
Route::get('/articles', 'ArticleController@articles')->middleware('SaveLastPage');
Route::get('/agencies/{service}', 'AgencyController@single')->middleware('SaveLastPage');
Route::get('/articles', 'ArticleController@articles')->middleware('SaveLastPage');
Route::get('/gallery', 'GalleryController@index')->middleware('SaveLastPage');
Route::get('/rules', 'RulesController@index')->middleware('SaveLastPage');
Route::get('/articles/{articleSlug}', 'ArticleController@single')->middleware('SaveLastPage');
Route::post('/rate', 'ArticleController@rate');
Route::get('/faq', 'HomeController@faq');
Route::get('/calculate', 'InstallmentController@showCalculate')->middleware('SaveLastPage');


//Payment
Route::post('/pay', 'PaymentController@pay')->name('pay');
Route::post('/payPrepayment', 'PaymentController@payPrepayment')->name('payPrepayment');
Route::get('/callback', 'PaymentController@callback')->name('pay.callback');
Route::post('/wallet-payment', 'WalletController@payment')->name('wallet-payment');
Route::post('/wallet-payment/checker', 'WalletController@paymentchecker')->name('wallet-payment.checker');

Route::get('/checkout/{factor}', 'CheckoutController@index')->name('checkout')->middleware('SaveLastPage');
//Factor
Route::middleware('auth')->group(function () {
    //Checkout
    Route::post('/checkout', 'CheckoutController@store')->name('checkout.store');
    Route::post('/upload_document', 'CheckoutController@upload_document')->name('checkout.upload');
    Route::get('/factorItem/{factorItem}', 'FactorItemController@show')->middleware('SaveLastPage');
    Route::post('/factorItem/request-contract-settlement', 'FactorItemController@requestContractSettlement')->name('site.factorItem.requestContractSettlement');
    Route::get('/factor/create', 'FactorController@create')->name('factor.create')->middleware('SaveLastPage');
    Route::get('/cart', 'CartController@index')->name('cart')->middleware('SaveLastPage');
    Route::post('/addToCart', 'CartController@add');
    Route::post('/deleteCart', 'CartController@delete');
    Route::post('/discount', 'CartController@discount')->name('discount');
    Route::get('/profile', 'ProfileController@index')->name('profile')->middleware('SaveLastPage');
    Route::get('/profile/detail', 'ProfileController@detail')->name('profile.detail')->middleware('SaveLastPage');
    Route::get('/profile/service', 'ProfileController@service')->name('profile.service')->middleware('SaveLastPage');
    Route::get('/profile/wallet', 'ProfileController@wallet')->name('profile.wallet')->middleware('SaveLastPage');
    Route::get('/profile/factor', 'ProfileController@factor')->name('profile.factor')->middleware('SaveLastPage');
    Route::get('/profile/factorItem', 'ProfileController@factorItem')->name('profile.factorItem')->middleware('SaveLastPage');
    Route::get('/profile/laleCard', 'ProfileController@laleCard')->name('profile.laleCard')->middleware('SaveLastPage');
    Route::get('/profile/notifications', 'ProfileController@notifications')->name('profile.notifications')->middleware('SaveLastPage');
    Route::get('/profile/notifications/read', 'ProfileController@notifications_read')->name('profile.notifications.read')->middleware('SaveLastPage');
    Route::post('/profile', 'ProfileController@update')->name('profile.update');
    Route::post('/useravatar', 'ProfileController@useravatar')->name('useravatar');
    Route::post('/dashboard/uploadPhotosTemporarily', 'Admin\AdminController@uploadPhotosTemporarily');
});
Route::get('/factor/{factor}', 'FactorController@index')->name('factor')->middleware('SaveLastPage');
Route::get('/payment/{payment}', 'FactorItemController@index')->name('payment')->middleware('SaveLastPage');
Route::get('/pf/{factor}', 'PaymentController@fastpay')->name('pay.fast');
Route::get('/pp/{payment}', 'PaymentController@fastPrePay')->name('pay.prepayment');
Route::post('/coupon', 'FactorController@coupon')->name('coupon');

//profile

//organization & representation
Route::get('/{organization}/services/{service}', 'OrganizationController@single')->middleware('SaveLastPage');
Route::get('/organizations/{organization}', 'OrganizationController@index')->middleware('SaveLastPage');
Route::get('/{representation}', 'RepresentationController@index')->middleware('SaveLastPage');
Route::get('/{representation}/link/{callcenter}', 'RepresentationController@index')->middleware('SaveLastPage');
Route::get('/{representation}/{service}', 'RepresentationController@single')->middleware('SaveLastPage');
Route::get('/{representation}/{service}/{callcenter}', 'RepresentationController@single')->middleware('SaveLastPage');

//Front Ajax
Route::post('/city/select', 'HomeController@cityselect');

Route::post('/dashboard/installment/calculate', 'Admin\TotalFactorItemInstallmentController@calculate');
