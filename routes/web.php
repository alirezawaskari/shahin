<?php

use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\Auth\AdminOtpController;
use App\Http\Controllers\Admin\BusinessTransferController;
use App\Http\Controllers\Admin\CallcenterController;
use App\Http\Controllers\Admin\CampaignController as CampaignControllerAdmin;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryPermissionController;
use App\Http\Controllers\Admin\CheckController;
use App\Http\Controllers\Admin\ContextController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeTermsController;
use App\Http\Controllers\Admin\FactorController;
use App\Http\Controllers\Admin\FactorItemController;
use App\Http\Controllers\Admin\FinancialPlansTypeController;
use App\Http\Controllers\Admin\GatewayController;
use App\Http\Controllers\Admin\GhorfeOnlineListController;
use App\Http\Controllers\Admin\PercentageAllocationEmployeeController;
use App\Http\Controllers\Admin\ProductOrderController;
use App\Http\Controllers\Admin\PromissoryController;
use App\Http\Controllers\Admin\RecruitmentNoticeController;
use App\Http\Controllers\Admin\ResumeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShortLinkController;
use App\Http\Controllers\Admin\site\ArticleController;
use App\Http\Controllers\Admin\site\BrandController;
use App\Http\Controllers\Admin\site\CategoryArticleController;
use App\Http\Controllers\Admin\site\ColorController;
use App\Http\Controllers\Admin\site\NewsController;
use App\Http\Controllers\Admin\site\WorkSampleController;
use App\Http\Controllers\Admin\SMSController;
use App\Http\Controllers\Admin\SmsTemplateController;
use App\Http\Controllers\Admin\MenuSettingController;
use App\Http\Controllers\Admin\SuspensionStandardController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\TicketControllerTest;
use App\Http\Controllers\Admin\TicketHistoryController;
use App\Http\Controllers\Admin\TotalFactorItemContractSettlementController;
use App\Http\Controllers\Admin\TotalFactorItemController;
use App\Http\Controllers\Admin\TotalFactorItemInstallmentController;
use App\Http\Controllers\Admin\TreasuryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestController;
use App\Models\Center;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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
//Auth Routes
Auth::routes();
Route::get('/pass1word/reset', [ResetPasswordController::class, 'resetform'])->name('reset_pass_form');
Route::post('/password/reset', [ResetPasswordController::class ,'reset'])->name('reset_pass');
Route::get('/register/check-user-is-register',[RegisterController::class,'checkUserIsRegister']);
Route::get('/login_by_mobile',[RegisterController::class,'showRegistrationForm'])->name('login_by_mobile');
Route::post('/fastregister', [RegisterController::class,'fastregister']);
//Campaign
Route::post('/user/sellingAccount', 'CampaignController@sellingAccount')->name('user.sellingAccount');
Route::get('/campaign/{slug:string}', [CampaignController::class,'campaignRegister'])->name('campaign');
Route::post('/campaign/register/insert/{campaign_id:int}', [CampaignController::class,'campaignRegisterInsert'])->name('campaign.register.insert');
Route::post('type/category', [CampaignController::class,'getTypeCategory']);
Route::post('category/service', [CampaignController::class,'getServiceCategory']);

//Route::post('loginRepresentation' , 'LoginController@representation')->name('login.representation');
//Route::post('registerRepresentation' , 'RegisterController@representation')->name('register.representation');

//Reset & Command Routes
Route::group([], function () {

    Route::get('/l/{link}', [ShortLinkController::class,'redirect']);
    Route::get('/factor-item/a-transfer', [AccountingController::class,'transfer']);
    Route::get('/contractElectronic', function () {
        return view('site.layouts.contractElectronic');
    });
    Route::get('/documentShare', function () {
        return view('site.document');
    });
    Route::get('/lagestic', function () {
        return redirect('/singleWebPage/logistic');
    });
    Route::get('/lagestic/monaghese1', function () {
        return redirect('/singleWebPage/logistic/monaghese1');
    });
    Route::get('/lagestic/tender-shares', function () {
        return redirect('/singleWebPage/logistic/tender-shares');
    });

    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
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

    Route::get('/fix-centers', function () {
      $centers= Center::where('centerable_type', Service::class)->get();
        foreach ($centers as $center) {
            $center->services()->sync(array($center->centerable_id));
      }
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

    Route::get('/managePendingChecks', function () {
        Artisan::call('pendingCheck:mange');
        return redirect()->back()->with([
            'manage_pending_checks' => 'success'
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

       // ini_set ('max_execution_time',  -1);

        Artisan::call('command:query');
        return redirect()->back()->with([
            'result' => 'success'
        ]);
    });
});
Route::get('test/pay',[TestController::class,'test']);

Route::get('/gettingIncomingSms', [SMSController::class,'gettingIncomingSms']);


//Routes for user
Route::middleware('guest')->prefix('/admin')->name('admin.')->group(function () {

    // نمایش فرم ورود
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login.show');

    // ورود با رمز عبور
    Route::post('login', [AdminLoginController::class, 'loginWithPassword'])
        ->name('login.password');

    // درخواست ارسال کد یکبار مصرف
    Route::post('login/otp/request', [AdminOtpController::class, 'requestCode'])
        ->name('login.otp.request');

    // اعتبارسنجی کد و ورود
    Route::post('login/otp/verify', [AdminOtpController::class, 'verifyCode'])
        ->name('login.otp.verify');

    // خروج — فقط برای کاربر authenticated
    Route::middleware('auth')->post('logout', [AdminLoginController::class, 'logout'])
        ->name('logout');
});

//Admin Routes
Route::group(['namespace' => 'Admin', 'prefix' => 'dashboard', 'middleware' => ['auth:web', 'checkActive', 'checkAdmin']], function () {
    Route::post('/business-transfer', [BusinessTransferController::class, 'store'])
        ->name('businessTransfer.store');

    //dashboard
    Route::get('/run-query', function () {
        Artisan::call('command:query');
        return redirect()->back()->with([
            'run_query' => 'success'
        ]);
    });
    Route::post('/create-user' , [UserController::class,'fetchUser'])->name('admin.createUser');
    Route::get('/logs' , [AdminController::class,'showLogs'])->name('admin.showLogs');
    Route::post('/sms/iran-sms-credit' , [SMSController::class,'fetchSmsCredit']);
    Route::get('/accounting/all' , [AccountingController::class,'index'])->name('accounting.all');
    Route::get('/accounting/filter-cash-debtors' , [AccountingController::class,'filterCashDebtors'])->name('accounting.filter.cashDebtors');
    Route::get('/accounting/filter-credit-debt' , [AccountingController::class,'filterCreditDebt'])->name('accounting.filter.creditDebt');
    Route::get('/accounting/filter-installment-debtors' , [AccountingController::class,'filterInstallmentDebtors'])->name('accounting.filter.installmentDebtors');
    Route::get('/accounting/show/{user}' , [AccountingController::class,'show'])->name('accounting.show');
    Route::get('/', [AdminController::class,'dashboard']);
    Route::get('/settings', [ContextController::class,'settings'])->name('settings.index');
    Route::patch('/settings/update-expire-dates', [ContextController::class,'updateExpireDates'])->name('settings.updateExpireDates');
    Route::patch('/settings/update-company-info',[SettingController::class, 'updateCompanyInfo'])->name('settings.updateCompanyInfo');
    Route::post('/settings/sms', [SettingController::class,'updateSmsSetting'])->name('settings.update.sms.setting');

    Route::resource('/campaign', CampaignControllerAdmin::class);
    Route::post('/campaign/from', [CampaignControllerAdmin::class,'createForm'])->name('campaign.create.form');
    Route::post('/campaign/company/{campaign_id}', [CampaignControllerAdmin::class,'createCompany'])->name('campaign.create.company');
    Route::get('/campaign/company/delete/{campaign_id}', [CampaignControllerAdmin::class,'deleteCompany'])->name('campaign.delete.company');

    // درگاه انتخابی
    Route::resource('gateway', GatewayController::class);

    // delete a Spatie media row by id (gallery images, etc.)
    Route::post('removeMedia', [AdminController::class,'removeMedia']);

    // stream a media file inline by id (temp uploads live on the private `local`
    // disk with no public url) — pdf renders in-browser, other types download.
    Route::get('viewMedia/{id}', [AdminController::class,'viewMedia'])->name('viewMedia');

    // نمونه کارها
    Route::post('removeImgWorkSample', [AdminController::class,'removeImgWorkSample']);
    // destroy is registered: the index blade renders a حذف form per row, so excluding it
    // made route('workSample.destroy') throw and the whole list page 500
    Route::resource('workSample',WorkSampleController::class)->except(['show']);
    Route::post('/admin/work-sample/get-items', [WorkSampleController::class, 'getItemsByType'])
        ->name('workSample.getItemsByType');

    Route::post('/service/fetch-serviceitem-by-service', [ServiceController::class,'fetchServiceItemsByServiceId']);
    Route::get('/dailyDesktop', [AdminController::class,'dailyDesktop']);
    Route::get('/set-link/{slug}', [AdminController::class,'setLink']);
    Route::get('/filter/{type}', [AdminController::class,'dashboard']);
    Route::post('/cities' , [AdminController::class,'cities']);
    Route::post('/fetch-user' , [AdminController::class,'user']);
    Route::post('/summernote-image' , [AdminController::class,'summernote_image']);
    Route::post('/uploadImage', [AdminController::class , 'uploadImage']);
    Route::get('/context/site-rules',[ContextController::class, 'fetchSiteRules'])->name('siteRules.index');
    Route::patch('/context/site-rules',[ContextController::class, 'updateSiteRules'])->name('siteRules.update');
    //callcenter
    Route::post('/callcenter/multi-delete', [CallcenterController::class, 'multiDelete'])->name('callcenter.delete.multi');
    Route::get('/callcenter/suspension', [CallcenterController::class, 'suspension'])->name('callcenter.suspension');
    Route::get('/callcenter/suspension/filter', [CallcenterController::class, 'filterSuspension']);
    Route::get('/callcenter/paid', [CallcenterController::class, 'paid'])->name('callcenter.paid');
    Route::get('/callcenter/cancellation', [CallcenterController::class, 'cancellation'])->name('callcenter.cancellation');
    Route::get('/callcenter/paid/filter', [CallcenterController::class, 'filterPaid']);
    Route::get('/callcenter/cancellation/filter', [CallcenterController::class, 'filterCancellation']);
    Route::get('/callcenter/desktop', [CallcenterController::class, 'desktop'])->name('callcenter.desktop');
    Route::get('/callcenter/desktop/filter', [CallcenterController::class, 'filterDesktop']);
    Route::get('/callcenter/filter', [CallcenterController::class, 'filter']);
    Route::post('/callcenter/registerForm', [CallcenterController::class, 'registerForm'])->name('callcenter.registerForm');
    Route::get('/callcenter/fetchUser', [CallcenterController::class, 'fetchUser']);
    // Category -> Product -> Variant cascade + default registration fee for the send-invoice form.
    Route::post('/callcenter/productsByCategory', [CallcenterController::class, 'productsByCategory']);
    Route::post('/callcenter/productAttributes', [CallcenterController::class, 'productAttributesByProduct']);
    Route::post('/callcenter/serviceRegisterPrice', [CallcenterController::class, 'serviceRegisterPrice']);
    Route::get('/callcenter/deleted', [CallcenterController::class, 'delete'])->name('callcenter.delete');
    Route::get('/callcenter/restore', [CallcenterController::class, 'restore'])->name('callcenter.restore');
    Route::post('/fastregister', [CallcenterController::class, 'fastregister'])->name('fastregister_callcenter');
    Route::post('/fastservice', [CallcenterController::class, 'fastservice'])->name('fastservice');
    Route::post('/reason', [CallcenterController::class, 'reason'])->name('callcenter.reason');
    Route::post('/callcenter/change', [CallcenterController::class, 'change'])->name('callcenter.change');
    Route::get('/tasks-requires', [CallcenterController::class, 'tasks_requires']);
    Route::post('/task-detail', [CallcenterController::class, 'taskDetail']);
    Route::post('/status-form', [CallcenterController::class, 'statusForm']);
    Route::post('/service-form', [CallcenterController::class, 'service_form']);
    Route::post('/service-form-global', [CallcenterController::class, 'service_form_global']);
    Route::post('/transfer-form', [CallcenterController::class, 'transfer_form']);
    Route::post('/task-transfer', [CallcenterController::class, 'task_transfer']);
    Route::post('/callcenter/closeTask', [CallcenterController::class, 'closeTask']);
    Route::post('/callcenter/followedTask', [CallcenterController::class, 'followedTask']);
    Route::resource('callcenter', CallcenterController::class);

    Route::get('short-link/filter', [ShortLinkController::class, 'filter']);
    Route::resource('short-link', ShortLinkController::class);

    Route::get('/disease/filter', [DiseaseController::class, 'filter'])->name('disease.filter');
    Route::resource('disease', DiseaseController::class);

    Route::get('employee-terms', [EmployeeTermsController::class, 'index'])->name('employee.terms.index');
    Route::post('employee-terms-accept', [EmployeeController::class, 'acceptTerms'])->name('employee.terms.accept');
    Route::post('employee-terms-store', [EmployeeTermsController::class, 'store'])->name('employee.terms.store');

    Route::resource('suspensionStandard', SuspensionStandardController::class);
    //factor
    Route::get('/factor/desktop', [FactorController::class, 'desktop'])->name('factor.desktop');
    Route::get('/system-income', [FactorController::class, 'systemIncome'])->name('system.income');
    Route::get('/factor-desktop', [FactorController::class, 'filterDesktop']);
    Route::get('/factorexel', [FactorController::class, 'exel'])->name('factor.exel');
    Route::get('/factorfilter', [FactorController::class, 'filter'])->name('factor.filter');
    Route::post('/factor-comment', [FactorController::class, 'comment'])->name('factor.comment');
    Route::post('/factor-manager', [FactorController::class, 'factor_manager']);
    Route::post('/factor/sms', [FactorController::class, 'sms'])->name('factor.sms');
    Route::get('/factors', [FactorController::class, 'filter']);
    Route::get('/factor/deleted', [FactorController::class, 'deletedList'])->name('factor.delete');
    Route::get('/factor/deleted/filter', [FactorController::class, 'filterDeletedList']);
    Route::post('/factor/restore', [FactorController::class, 'restore']);
    Route::post('/factor/sendMe/{factor}', [FactorController::class, 'sendMe'])->name('factor.sendMe');
    Route::post('/factor-update', [FactorController::class, 'update']);

    Route::resource('/factor', FactorController::class);
    Route::resource('/category-permission', CategoryPermissionController::class);
    //check
    Route::get('/check/repair', [CheckController::class, 'repair']);
    Route::get('/check/filter', [CheckController::class, 'filter']);
    Route::post('/check/update-status', [CheckController::class, 'updateStatus']);
    Route::post('/check/update/{check}', [CheckController::class, 'update'])->name('check.update');
    Route::resource('/check', CheckController::class);
    //promissory
    Route::get('/promissory/repair', [PromissoryController::class, 'repair']);
    Route::get('/promissory/filter', [PromissoryController::class, 'filter']);
    Route::post('/promissory/update-status', [PromissoryController::class, 'updateStatus']);
    Route::post('/promissory/update/{promissory}', [PromissoryController::class, 'update'])->name('promissory.update');
    Route::resource('/promissory', PromissoryController::class);
    //treasury
    Route::get('/treasury/index', [TreasuryController::class, 'calendar'])->name('treasury.calendar');

    // SMS templates
    Route::get('/smsTemplate/filter', [SmsTemplateController::class, 'filter']);
    Route::get('/menuSetting/filter', [MenuSettingController::class, 'filter']);
    Route::post('/permissions/updateSubMenu', [MenuSettingController::class, 'updateSubMenu'])->name('permissions.updateSubMenu');
    Route::resource('/smsTemplate', SmsTemplateController::class);
    Route::resource('/menuSetting', menuSettingController::class);

    //total factor item
    Route::post('/totalFactorItem/suspension', [TotalFactorItemController::class, 'suspension'])->name('totalFactorItem.suspension');
    Route::get('/totalFactorItem/desktop', [TotalFactorItemController::class, 'desktop'])->name('totalFactorItem.desktop');
    Route::get('/totalFactorItem/desktop/filter', [TotalFactorItemController::class, 'filterDesktop']);
    Route::post('/totalFactorItem/request-contract-settlement', [TotalFactorItemController::class, 'requestContractSettlement'])->name('totalFactorItem.requestContractSettlement');
    Route::post('/totalFactorItem/contract-settlement', [TotalFactorItemController::class, 'contractSettlement'])->name('totalFactorItem.contractSettlement');
    Route::get('/totalFactorItem/{totalFactorItem}/installment', [TotalFactorItemController::class, 'installments']);
    Route::get('/totalFactorItem/checks/{totalFactorItem}', [TotalFactorItemController::class, 'checks'])->name('totalFactorItem.checks');
    Route::get('/totalFactorItem/promissory/{totalFactorItem}', [TotalFactorItemController::class, 'promissories'])->name('totalFactorItem.promissories');
    Route::post('/totalFactorItem/upload-document', [TotalFactorItemController::class, 'uploadDocument'])->name('totalFactorItem.uploadDocument');
    Route::post('/totalFactorItem/save-extra-pay', [TotalFactorItemController::class, 'saveExtraPay'])->name('totalFactorItem.saveExtraPay');
    Route::post('/totalFactorItem/store-impunity', [TotalFactorItemController::class, 'storeImpunity'])->name('totalFactorItem.storeImpunity');
    Route::delete('/totalFactorItem/destroy-impunity/{impunity}', [TotalFactorItemController::class, 'destroyImpunity'])->name('totalFactorItem.destroyImpunity');
    Route::get('/totalFactorItem/reports', [TotalFactorItemController::class, 'reports'])->name('totalFactorItem.reports');
    Route::get('/totalFactorItem/reports/filter', [TotalFactorItemController::class, 'filterReport']);
    Route::get('/totalFactorItem/reports/desktop', [TotalFactorItemController::class, 'desktopReports']);
    Route::get('/totalFactorItem/reports/desktop/filter', [TotalFactorItemController::class, 'filterDesktopReport']);
    Route::get('/totalFactorItem/filter', [TotalFactorItemController::class, 'filter'])->name('totalFactorItem.filter');
    Route::post('/totalFactorItem/sendInitialLink', [TotalFactorItemController::class, 'sendInitialLink'])->name('totalFactorItem.sendInitialLink');
    Route::post('/totalFactorItem/change-status', [TotalFactorItemController::class, 'changeStatus'])->name('totalFactorItem.changeStatus');
    Route::post('/totalFactorItem/change-status-mali', [TotalFactorItemController::class, 'changeStatusMaliFactor']);
    Route::post('/totalFactorItem/payment', [TotalFactorItemController::class, 'payment'])->name('totalFactorItem.sendPayment');
    Route::post('/totalFactorItem/save-installments-paid', [TotalFactorItemController::class, 'saveInstallmentsPaid'])->name('totalFactorItem.saveInstallment');
    Route::post('/totalFactorItem/return-payment', [TotalFactorItemController::class, 'returnPayment'])->name('totalFactorItem.returnPayment');
    Route::get('/totalFactorItem/calculate', [TotalFactorItemInstallmentController::class, 'showCalculate'])->name('totalFactorItem.calculate');
    Route::post('/totalFactorItem/calculate', [TotalFactorItemController::class, 'updateCalculate'])->name('totalFactorItem.calculate');
    Route::post('/totalFactorItem/close', [TotalFactorItemController::class, 'close']);
    Route::resource('totalFactorItem', TotalFactorItemController::class);
    Route::get('/totalFactorItemInstallment/filter', [TotalFactorItemInstallmentController::class, 'filter'])->name('totalFactorItemInstallment.filter');
    Route::resource('totalFactorItemInstallment', TotalFactorItemInstallmentController::class);
    //total factor item contract settlement
    Route::post('/totalFactorItemContractSettlement', [TotalFactorItemContractSettlementController::class, 'confirm'])->name('totalFactorItemContractSettlement.confirm');

// Financial plans types
    Route::post('/financialPlansType/selectByCategory', [CategoryController::class, 'child'])->name('category.get.child');
    Route::post('/financialPlansType/setByPaymentType', [FinancialPlansTypeController::class, 'setFinancialPlansTypeByPaymentType']);
    Route::get('/financialPlansType/filter', [FinancialPlansTypeController::class, 'filter'])->name('home.financialPlansType.filter');
    // Route::get('/financialPlansType/{service}', 'FinancialPlansTypeController@getByService')->name('service.financialPalnsTypes');

    Route::resource('financialPlansType' , 'FinancialPlansTypeController');
    //contract form
    Route::resource('contractForm', 'ContractFormController');
    Route::get('/contractForm/items/{contract_form}', 'ContractFormController@formItems')->name('contract.formItems');
    Route::get('/contract_before_form_item_create/{contract_form}', 'contractFormController@formItemCreate')->name('contract.before.formItemCreate');
    //factor item
    Route::post('/factorItem/close', 'FactorItemController@close');
    Route::get('/factorItem/desktop', 'FactorItemController@desktop')->name('factorItem.desktop');
    Route::get('/factorItem/desktop/filter' , 'FactorItemController@filterDesktop');
    Route::get('/factorItem/filter' , [FactorItemController::class,'filter']);
    Route::get('/factorItem/referral/{factorItem}' , 'FactorItemController@referral')->name('factorItem.referral');
    Route::post('/factorItem/startWork/{factorItem}' , 'FactorItemController@startWork')->name('factorItem.startWork');
    Route::get('/factorItem/getForm/{factorItem}' , 'FactorItemController@getForm')->name('factorItem.getForm');
    Route::post('/factorItem/setRegisterationInitialConsutation', 'FactorItemController@setRegisterationInitialConsutation')->name('factorItem.setRegisterationInitialConsutation');

    Route::post('/factorItem/transfer' , 'FactorItemController@transfer');
    Route::post('/factorItem/update-information', 'FactorItemController@updateInformation')->name('factorItem.information');
    Route::post('/factorItem/createFactor/{factorItem}', [FactorItemController::class,'createFactor'])->name('factorItem.createFactor');
    Route::post('/factorItem/sendNotificationRecordingFactor/{factorItem}', [FactorItemController::class,'sendNotificationRecordingFactor'])->name('factorItem.sendNotificationRecordingFactor');
    Route::post('/factorItem/update-calculate', 'FactorItemController@updateCalculate')->name('factorItem.calculate');
    Route::post('/factorItem/payment', 'FactorItemController@payment')->name('factorItem.sendPayment');
    Route::post('/factorItem/return-payment', 'FactorItemController@returnPayment')->name('factorItem.returnPayment');
    Route::post('/factorItem/upload-document', 'FactorItemController@uploadDocument')->name('factorItem.document');
    Route::post('/factorItem/completion-file-work', 'FactorItemController@completionFileWork')->name('factorItem.completion');
    Route::get('/previous-step/{id}', 'FactorItemController@previousStep');
    Route::get('/next-step/{id}', 'FactorItemController@nextStep');
    Route::post('/save-image', 'FactorItemController@saveImage');
    Route::get('/factorItem/delete-service-tariff/{id}' , 'FactorItemController@deleteServiceTariff')->name('factorItem.deleteServiceTariff');
    Route::post('/factorItem/update-product' , 'FactorItemController@updateProduct')->name('factorItem.updateProduct');
    Route::post('/factorItem/updateServiceTariff/{factorItem}' , [FactorItemController::class , 'updateServiceTariff'])->name('factorItem.updateServiceTariff');
    Route::get('/factorItem/expire', 'FactorItemController@expire');
    Route::post('/factorItem/sendInitialLink/{factorItem}', 'FactorItemController@sendInitialLink')->name('factorItem.sendInitialLink');
    Route::post('/factorItem/change-statusFactor', 'FactorItemController@changeStatusFactor')->name('factorItem.changeStatus');
    Route::resource('factorItem', FactorItemController::class);
    Route::post('/factoritem/turn-ratings/{factorItem}' , 'FactorItemController@turnRatings')->name('factorItem.turnRatings');
    Route::post('/factoritem/set-center' , 'FactorItemController@setCenter')->name('factorItem.setCenter');
    Route::post('/factoritem/change-factoritemable' , 'FactorItemController@changeFactoritemable')->name('factorItem.changeFactoritemable');
    Route::post('/factoritem/suspension', 'FactorItemController@suspension')->name('factorItem.suspension');
    Route::get('/factorItemProduct/filter' , 'FactorItemProductController@filter');
    Route::post('/factorItem/change-financial-plans-type/{factorItem}' , 'FactorItemController@changeFinancialPlansType')->name('factorItem.changeFinancialPlansType');

    Route::resource('factorItemProduct', 'FactorItemProductController');
    Route::resource('factorItemInstallment', 'FactorItemInstallmentController');
    Route::resource('factorItemServiceTariff', 'FactorItemServiceTariffController');

    //product orders - {factor} binds on resnumber (Factor::getRouteKeyName), so the literal
    //`filter` route has to be declared before it or it would be swallowed as an order number
    Route::get('/productOrder/filter', [ProductOrderController::class, 'filter'])->name('productOrder.filter');
    Route::get('/productOrder', [ProductOrderController::class, 'index'])->name('productOrder.index');
    Route::get('/productOrder/{factor}', [ProductOrderController::class, 'show'])->name('productOrder.show');
    Route::post('/productOrder/{factor}/address', [ProductOrderController::class, 'setAddress'])->name('productOrder.setAddress');
    Route::post('/productOrder/{factor}/address/store', [ProductOrderController::class, 'storeAddress'])->name('productOrder.storeAddress');
    Route::patch('/productOrder/{factor}/address/{address}', [ProductOrderController::class, 'updateAddress'])->name('productOrder.updateAddress');
    Route::delete('/productOrder/{factor}/address/{address}', [ProductOrderController::class, 'destroyAddress'])->name('productOrder.destroyAddress');
    Route::post('/productOrder/{factor}/shipping-cost', [ProductOrderController::class, 'setShippingCost'])->name('productOrder.shippingCost');
    Route::post('/productOrder/{factor}/ship', [ProductOrderController::class, 'ship'])->name('productOrder.ship');

    //purchase invoice
    Route::get('/purchaseInvoice/desktop', 'PurchaseInvoiceController@desktop')->name('purchaseInvoice.desktop');
    Route::get('/purchaseInvoice/desktop/filter', 'PurchaseInvoiceController@filterDesktop');
    Route::get('/purchaseInvoice/filter', 'PurchaseInvoiceController@filter');
    Route::post('/purchaseInvoice/save-report/{purchaseInvoice}', 'PurchaseInvoiceController@saveReport')->name('purchaseInvoice.report');
    Route::post('/purchaseInvoice/save-payment/{purchaseInvoice}', 'PurchaseInvoiceController@savePayment')->name('purchaseInvoice.savePayment');
    Route::post('/purchaseInvoice/return-payment/{purchaseInvoice}', 'PurchaseInvoiceController@returnPayment')->name('purchaseInvoice.returnPayment');
    Route::post('/purchaseInvoice/confirm/{purchaseInvoice}', 'PurchaseInvoiceController@confirm')->name('purchaseInvoice.confirm');
    Route::get('/purchaseInvoice/reject/{purchaseInvoice}', 'PurchaseInvoiceController@reject')->name('purchaseInvoice.reject');
    Route::post('/purchaseInvoice/change-status/{purchaseInvoice}', 'PurchaseInvoiceController@changeStatus')->name('purchaseInvoice.changeStatus');
    Route::post('/purchaseInvoice/send-product/{purchaseInvoice}', 'PurchaseInvoiceController@sendProduct')->name('purchaseInvoice.sendProduct');
    Route::get('/create-product-purchaseInvoice/{purchaseInvoice}', 'PurchaseInvoiceController@createProduct');
    Route::post('/store-product/{purchaseInvoice}', 'PurchaseInvoiceController@storeProduct')->name('purchaseInvoice.product');
    Route::post('/purchaseInvoice/save-report/{purchaseInvoice}', 'PurchaseInvoiceController@saveReport')->name('purchaseInvoice.report');
    Route::get('/purchase-invoice-installment-delete/{purchaseInvoiceInstallment}', 'PurchaseInvoiceInstallmentController@destroy');
    Route::resource('purchaseInvoice', 'PurchaseInvoiceController');
    Route::resource('supplier', 'SupplierController');
    //employee
    Route::get('/employee/financialReportEmployeeList', 'EmployeeController@financialReportEmployeeList')->name('employee.financialReport.list');
    Route::get('/employee/filter', 'EmployeeController@filter')->name('employee.filter');
    // phone lookup behind the new-employee form; must stay above the employee resource so
    // /employee/searchUser is not swallowed by /employee/{employee}
    Route::get('/employee/searchUser', 'EmployeeController@searchUser')->name('employee.searchUser');
    Route::get('/seller/filter', 'EmployeeController@sellerFilter')->name('seller.filter');
    Route::get('/employee-financial-report/{employee}' , 'EmployeeController@financialReport')->name('employee.financial.report');
    Route::get('/employee-wallet/{user}/{key}' , 'EmployeeController@walletTransaction')->name('employee.wallet');
    Route::get('/employee-performance-detail/{user}' , 'EmployeeController@performanceDetail')->name('employee.performanceDetail');
    Route::get('/employee-performance-detail-filter/{user}' , 'EmployeeController@performanceDetailFilter');
    Route::get('/employee-wallet-filter/{user}/{key}' , 'EmployeeController@walletTransactionFilter');
    Route::get('/employee/resume/{employee}' , 'EmployeeController@editResume')->name('employee.resume.edit');
    Route::post('/employee/resume/{employee}' , 'EmployeeController@updateResume')->name('employee.resume.update');
    Route::post('/employee/profile/{employee}' , 'EmployeeController@updateProfile')->name('employee.profile.update');
    Route::get('/employee/contract/{employee}' , 'EmployeeController@showContract')->name('employee.contract.show');
    Route::post('/employee/update/contract/{employee}' , 'EmployeeController@updateContract')->name('employee.update.contract');
    Route::post('/employee/sendConfirmContractCode', [EmployeeController::class,'sendConfirmContractCode']);
    Route::post('/employee/verificationConfirmContractCode', 'EmployeeController@verificationConfirmContractCode');
    Route::resource('employee' , 'EmployeeController');
    //percentageAllocationEmployee
    Route::get('/dashboard/percentageAllocationEmployee/tree', [PercentageAllocationEmployeeController::class, 'tree']);
    Route::get('/percentageAllocationEmployee/filter', 'PercentageAllocationEmployeeController@filter')->name('percentageAllocationEmployee.filter');
    Route::get('/percentageAllocationEmployee/report', 'UserPerformanceController@report')->name('percentageAllocationEmployee.report');
    Route::get('/percentageAllocationEmployee/report/filter', 'UserPerformanceController@reportFilter')->name('percentageAllocationEmployee.report.filter');
    Route::resource('percentageAllocationEmployee' , 'PercentageAllocationEmployeeController');
    //user
    Route::get('/admin-user' , 'UserController@admin')->name('user.admin');
    Route::get('/user-deleted' , 'UserController@delete')->name('user.delete');
    Route::get('/user-restore' , 'UserController@restore')->name('user.restore');
    Route::get('/user/user-histories/{user}' , 'UserController@fetchUserHistories')->name('user.histories');
    Route::get('/user/filter', 'UserController@filter')->name('user.filter');
    Route::post('/user/sendPassword' , 'UserController@sendPassword');
    Route::get('/user/creditDetail/{user}' , 'UserController@creditDetail');
    Route::get('/user/login/{user}' , 'UserController@login');
    Route::resource('user', 'UserController');
    Route::post('/city/select', 'UserController@cityselect')->name('city.select');
    Route::post('/user/select', 'UserController@userselect')->name('user.select');
    Route::get('/users' , 'UserController@filter');
    Route::get('/sellers' , 'EmployeeController@sellers')->name('sellers');
    Route::post('/user-delete' , 'UserController@user_delete');
    Route::post('/user-delete-check' , 'UserController@user_delete_check');
    Route::post('/user-manager' , 'UserController@user_manager');
    //userCreditDetail
    Route::resource('userCreditDetail' , 'UserCreditDetailController');
    Route::post('/userCreditDeetailStore/{user}' , 'UserCreditDetailController@store')->name('userCreditDetail.store');
    //role
    Route::get('/role-create' , 'RoleController@create')->name('role.create');
    Route::get('/role/get-user-by-role' ,[RoleController::class,'getUserByRole'])->name('role.getUser');
    Route::get('role/filter', [RoleController::class,'filter']);
    Route::get('role/get', [RoleController::class,'getRoles']);
    Route::resource('role', 'RoleController');
    Route::resource('permission', 'PermissionController');


    //ticket
    Route::post('/ticket/refer', [TicketController::class,'refer'])->name('ticket.refer');
    Route::get('/ticket/all/filter', [TicketController::class,'filterAll']);
    Route::get('/ticket/send/filter', [TicketController::class,'filterSend']);
    Route::get('/ticket/received/filter',[TicketController::class,'filterReceived']);
    Route::get('/ticket/desktop/filter',[TicketController::class,'filterDesktop']);
    Route::get('/ticket/refer/filter',[TicketController::class,'filterRefer']);

    Route::get('/ticket/create',[TicketController::class,'create'])->name('ticket.create');
    Route::post('/ticket/store',[TicketController::class,'store'])->name('ticket.store');
    Route::get('/ticket/{ticket}/edit',[TicketController::class,'edit'])->name('ticket.edit');
    Route::post('/ticket/{ticket}/update',[TicketController::class,'update'])->name('ticket.update');
    Route::post('/ticket/{ticket}/destroy',[TicketController::class,'destroy'])->name('ticket.destroy');
    Route::resource('ticket/history', TicketHistoryController::class);
    Route::get('/ticket/{type}',[TicketController::class,'index'])->name('ticket.index');

    //service & category
    Route::post('/service/selectByCategory', 'ServiceController@selectByCategory');
    Route::get('/service/filter', 'ServiceController@filter');
    Route::get('/service/indexTariffs/{service}', 'ServiceController@getIndexTariffs');
    Route::get('/service/financialPlansType/{id}', 'ServiceController@getFinancialPlansType');
    Route::resource('service', ServiceController::class);
    //serviceItem
    Route::get('/service/serviceItem/create/{service}', 'ServiceItemController@createByService')->name('service.serviceItem.create');
    Route::post('/service/serviceItem/store/{service}', 'ServiceItemController@storeByService')->name('service.serviceItem.store');
    Route::get('/service/serviceItem/{service}', 'ServiceItemController@serviceItemList')->name('service.serviceItem.all');
    Route::resource('serviceItem', 'ServiceItemController');
    //serviceIndexTariff
    Route::get('/serviceIndexTariff/filter', 'ServiceIndexTariffController@filter');
    Route::get('/serviceIndexTariff/getPurchasePrice/{id}', 'ServiceIndexTariffController@getPurchasePrice');
    Route::post('/serviceIndexTariff/createOrUpdate', 'ServiceIndexTariffController@createOrUpdate');
    Route::resource('serviceIndexTariff', 'ServiceIndexTariffController');
    //taiffs
    Route::get('tariff/filter', [TariffController::class,'filter']);
    Route::resource('tariff', TariffController::class);

    Route::group(['namespace' => 'site', 'prefix' => 'site'], function () {
        Route::resource('/category', 'CategoryController');
        Route::get('/category/sub/{cat_id}', 'CategoryController@subCategory')->name('category.subCategory');
        Route::post('/findChild', 'CategoryController@findChild');
        Route::post('/changePosition', 'CategoryController@changePosition');
        Route::get('/categoryPosition/{id}', 'CategoryController@categoryPosition')->name('categoryPosition');
        Route::post('/removeCatMenu', 'CategoryController@removeCatMenu');

        Route::resource('/attributeGroup', 'AttributeGroupController');
        Route::get('/createByCategory/{cat_id}', 'AttributeGroupController@createByCategory')->name('attributeGroup.createByCategory');

        Route::resource('/attribute', 'AttributeController');
        Route::get('/attribute/create/{id}', 'AttributeController@createAttribute')->name('createAttribute');
        Route::resource('/attributeItem', 'AttributeItemController');
        Route::get('/attributeItem/create/{id}', 'AttributeItemController@createAttributeItem')->name('createAttributeItem');
        Route::post('/addRequestAttributeItem', 'AttributeItemController@addRequestAttributeItem');
        Route::get('/attributeRequest/suggest', 'AttributeItemController@suggest')->name('attributeRequest.suggest');

        //brand
        Route::get('/brand/filter', [BrandController::class,'filter'])->name('brand.filter');
        Route::resource('/brand', 'BrandController');
        Route::get('/createByCatId/{id}', 'BrandController@createByCatId')->name('brand.createByCatId');
        Route::get('/statusBrand/{brand}', 'BrandController@statusBrand')->name('statusBrand');
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
        Route::get('/comments/desktop', 'CommentController@desktop');

        Route::resource('/coupon', 'CouponController');
        Route::resource('/couponItem', 'CouponItemsController');
        Route::post('/coupon/statusCoupon', 'CouponController@statusCoupon');
        Route::get('/coupons/discountVarietyProduct', 'CouponController@discountVarietyProduct')->name('coupons.discountVarietyProduct');
        Route::get('/createByCouponId/{id}', 'CouponItemsController@createByCouponId')->name('couponItem.createByCouponId');
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

    // No create/store: a "درباره ما" page belongs to an owner that already exists, it is
    // never made from the panel. Declared twice (see the `about` resource further down);
    // both have to carry the exclusion or the route comes back.
    Route::resource('/about', 'AboutController')->except(['create', 'store']);
    Route::get('/userInvite', 'UserInviteController@index')->name('userInvite.index');
    Route::resource('/inviteCategory', 'InviteCategoryController');
    Route::post('/searchTitleProduct', 'InviteCategoryController@searchTitleProduct');

    Route::get('/service_before_form/{service}', 'ServiceController@beforeForm')->name('service.before.form');
    Route::get('/service_before_form_create/{service}', 'ServiceController@beforeFormCreate')->name('service.before.form.create');
    Route::get('/service_before_form_item/{beforeform}', 'ServiceController@beforeFormItem')->name('service.before.form.item');
    Route::get('/service_before_form_item_create/{beforeform}', 'ServiceController@beforeFormItemCreate')->name('service.before.form.item.create');
    Route::post('/service/form', 'ServiceController@form');
    Route::post('/service/fetchForm', 'ServiceController@fetchForm');
    Route::resource('before_form', 'BeforeFormController')->except(['index', 'create', 'show']);
    Route::resource('before_form_item', 'BeforeFormItemController')->except(['index', 'create', 'show']);
    Route::resource('serviceItem', 'ServiceItemController');

    //installment
    Route::resource('/installment', 'InstallmentController');
    Route::get('/createInstallmentCategory/{id}', 'InstallmentController@createCategory');
    Route::get('/createInstallmentService/{id}', 'InstallmentController@createService');
    Route::get('/installment/service/{id}', 'InstallmentController@serviceInstallment');
    Route::get('/installment/detail/service/{id}', 'InstallmentController@serviceInstallmentDetail')->name('installment.detail.service');
    Route::post('/installment/finish', 'InstallmentController@finishInstallment')->name('finishInstallment');

    //products
    Route::resource('product', 'ProductController');
    Route::post('/product/store', 'ProductController@store');
    Route::get('/find/product', 'ProductController@findProduct')->name('findProduct');

    Route::post('/searchTransferProduct', 'ProductController@searchTransferProduct');
    Route::post('/selectTransferProduct', 'ProductController@selectTransferProduct');
    Route::post('/centerSellToo', 'ProductController@centerSellToo');

    //service tariffs
    Route::post('/serviceTariff/selectByTariff', 'ServiceTariffController@selectByTariff');
    Route::post('/serviceTariff/getSessionNumber', 'ServiceTariffController@getSessionNumber');
    Route::get('/serviceTariff/filter', 'ServiceTariffController@filter')->name('serviceTariff.filter');
    Route::post('serviceTariff/store', 'ServiceTariffController@store');
    Route::post('serviceTariff/update/{serviceTariff}', 'ServiceTariffController@update');
    Route::resource('serviceTariff', 'ServiceTariffController');

    //allocationEmployeeShareCenters
    Route::resource('allocationEmployeeShareCenter', 'AllocationEmployeeShareCenterController');

    //storeroom
    Route::resource('storeRoom', 'StoreRoomController');
    Route::get('/subWarehouses/{id}', 'StoreRoomController@subWarehouses')->name('subWarehouses');
    Route::get('/storeRoomGood/{id}', 'StoreRoomController@storeRoomGood')->name('storeRoomGood');
    Route::get('/editStoreRoomGood/{id}/{product_id}', 'StoreRoomController@editStoreRoomGood');
    Route::get('/storeRoomMaterial/{id}', 'StoreRoomController@storeRoomMaterial')->name('storeRoomMaterial');
    Route::get('/editStoreRoomMaterial/{id}/{product_id}', 'StoreRoomController@editStoreRoomMaterial');
    Route::delete('/productStoreRoom/{id}', 'StoreRoomController@destroyProductStoreRoom')->name('productStoreRoom.destroy');
    Route::patch('/productStoreRoomUpdate/{id}', 'StoreRoomController@productStoreRoomUpdate')->name('productStoreRoomUpdate');

    //show store Room Information in modal and edit it in seller modal
    Route::get('/getProductStoreRoom/{product_id}/{id}','StoreRoomController@productStoreRoomByProductId')->name('getProductStoreRoom');
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
    Route::post('/saveChangeVisitProduct','ProductController@saveChangeVisitProduct');

    //organization & representation
    Route::get('/organization/createUser', 'OrganizationController@createUser')->name('organization.user.create');
    Route::post('/organization/storeUser', 'OrganizationController@storeUser')->name('organization.user.store');
    Route::get('/organization/filter', 'OrganizationController@filter')->name('organization.filter');
    Route::get('/organization/items', 'OrganizationController@items')->name('organization.items');
    Route::resource('organization', 'OrganizationController');
    Route::resource('representation', 'RepresentationController');
    //ghorfeOnlineList
    // Spatie gallery save (replaces legacy getGallery + galleryUpdate).
    Route::post('ghorfeOnlineList/gallerySave/{ghorfeOnlineList}', 'GhorfeOnlineListController@gallerySave')->name('ghorfeOnlineList.gallerySave');
    Route::post('ghorfeOnlineList/officialInfoUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@officialInfoUpdate')->name('ghorfeOnlineList.officialInfoUpdate');
    Route::post('ghorfeOnlineList/officialInfoUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@officialInfoUpdate')->name('ghorfeOnlineList.officialInfoUpdate');
    Route::post('ghorfeOnlineList/contentUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@contentUpdate')->name('ghorfeOnlineList.contentUpdate');
    Route::post('ghorfeOnlineList/packageUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@packageUpdate')->name('ghorfeOnlineList.packageUpdate');
    Route::post('ghorfeOnlineList/menuUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@menuUpdate')->name('ghorfeOnlineList.menuUpdate');
    Route::post('ghorfeOnlineList/question/{ghorfeOnlineList}', [GhorfeOnlineListController::class,'question'])->name('ghorfeOnlineList.question');
    Route::post('ghorfeOnlineList/themeUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@themeUpdate')->name('ghorfeOnlineList.themeUpdate');
    Route::post('ghorfeOnlineList/categoryUpdate/{ghorfeOnlineList}', 'GhorfeOnlineListController@categoriesGhorfe')->name('ghorfeOnlineList.categoriesGhorfe');
    Route::get('ghorfeOnlineList/users', 'GhorfeOnlineListController@showUsersList')->name('ghorfeOnlineList.users');
    Route::get('ghorfeOnlineList/users/filter', 'GhorfeOnlineListController@filterUsers')->name('ghorfeOnlineList.users.filter');

    Route::resource('ghorfeOnlineList', 'GhorfeOnlineListController');
    //singleWebPage
    Route::resource('singleWebPage', 'SingleWebPageController');
    //center
    Route::get('/center-service/{center}' , 'CenterController@service')->name('center.service');
    Route::get('/center/filter', 'CenterController@filter')->name('center.filter');
    Route::get('/center/financialReport/{center}' , 'CenterController@financialReport')->name('center.financialReport');
    Route::get('/center/{center}/{service}/serviceTariff', 'CenterController@serviceTariffs')->name('center.serviceTariff');
    Route::get('/center/{center}/{service}/serviceTariff/create', 'CenterController@createServiceTariff');
    Route::get('/doctorsByCenter/{center_id}', 'CenterController@doctorsByCenter')->name('doctorsByCenter');
    Route::get('/productsByCenterId/{id}', 'CenterController@productsByCenterId')->name('productsByCenterId');
    Route::get('/center/products/{id}/{center_id}/edit', 'CenterController@productEdit')->name('center.products.edit');
    Route::patch('/center/products/update/{product}', 'CenterController@productUpdate')->name('center.products.update');
    Route::get('/getCenterCategory', 'CenterController@getCenterCategory');
    Route::post('/center/selectByService', 'CenterController@getByService');

    Route::resource('center', 'CenterController');

    //center request
    Route::get('/centerRequest/filter', 'CenterRequestController@filter')->name('centerRequest.filter');
    Route::resource('centerRequest', 'CenterRequestController');

    //fullCalendar
    Route::resource('/fullCalendar', 'CalendarController');
    Route::get('/allCalenderEvent', 'CalendarController@allCalenderEvent');
    Route::get('/fullCalendarDoctor/{user_id}', 'CalendarController@fullCalendarDoctor')->name('fullCalendarDoctor');

    Route::get('/list-product-storeRoom/{storeRoom}' , 'StoreRoomController@listProduct')->name('storeRoom.list.product');
    Route::get('/filter-product-storeRoom/{storeRoom}', 'StoreRoomController@filter')->name('storeRoom.product.filter');

    Route::post('/ckeditor/upload', 'CkeditorController@upload')->name('ckeditor.upload');

    Route::resource('/products', 'ProductController');
    Route::get('/products/visit/{id}', 'ProductController@visit');
    Route::get('/products/{id}/{status_id}/status', 'ProductController@status');
    Route::post('/assignSimilarProduct','ProductController@assignSimilarProduct');
    Route::post('/getProduct','ProductController@getProduct');

    Route::get('/find/product', 'ProductController@find');

    Route::get('/manage/product/createGood', 'ProductController@create')->name('createGood');
    Route::get('/manage/product/createGood/{type}/{id}', 'ProductController@createByValues')->name('createGoodByValues');

    Route::get('/manage/product/createMaterials', 'ProductController@createMaterials')->name('createMaterials');
    Route::get('/manage/product/createMaterials/{type}/{id}', 'ProductController@createMaterialsByValues')->name('createMaterialsByValues');

    Route::post('/product/storeMaterials', 'ProductController@storeMaterials')->name('products.storeMaterials');

    Route::resource('/product/price', 'PriceProductController');
    Route::post('/product/price/storeWithoutAttr', 'PriceProductController@storeWithoutAttr');

    Route::get('/product/pricesList/{id}', 'PriceProductController@pricesList');
    Route::get('/product/pricesListWithoutAttribute/{id}', 'PriceProductController@pricesListWithoutAttribute');
    Route::post('/product/updateProductAttribute', 'PriceProductController@updateProductAttribute');
    Route::post('/product/returnProductAttribute', 'PriceProductController@returnProductAttribute');

    Route::post('/product/searchProductTitle', 'ProductController@searchProductTitle');
    Route::get('/product/editInformation/{id}', 'ProductController@editInformation')->name('editInformation');

    Route::post('/saveChangeVisitProduct','ProductController@saveChangeVisitProduct');

    Route::resource('/periodicPrices','PeriodicPriceController');
    Route::post('/periodicPrices/storeProductIds','PeriodicPriceController@storeProductIds');

    Route::post('/periodicPrices/removeProductAttribute','PeriodicPriceController@removeProductAttribute');
    Route::post('/periodicPrices/checkDate','PeriodicPriceController@checkDate');
    Route::get('/periodicPricesProduct/completeSelection','PeriodicPriceController@completeSelection');
    Route::post('/periodicPrices/deleteProductAttribute','PeriodicPriceController@deleteProductAttribute');

//    Route::resource('/centerProducts', 'CenterProductController');
//    Route::get('/centerProducts/{product_id}/{id}', 'CenterProductController@centerProducts');

    Route::resource('/pack', 'PackController');
    Route::post('/getProductsPack', 'PackController@getProductsPack');

    //session
    Route::get('/session/filter' , 'SessionController@filter');
    Route::get('/session/desktop-confirm' , 'SessionController@desktopConfirm')->name('session.desktop.confirm');
    Route::get('/session/desktop-confirm/filter' , 'SessionController@filterDesktopConfirm');
    Route::get('/session/desktop-complete' , 'SessionController@desktopComplete')->name('session.desktop.complete');
    Route::get('/session/desktop-complete/filter' , 'SessionController@filterDesktopComplete');
    Route::get('/session/trunRatings/desktop-complete/filter' , 'SessionController@filterDesktopCompleteTurnRatings');
    Route::get('/session/confirm/{session}' , 'SessionController@confirm')->name('session.confirm');
    Route::post('/session/update' , 'SessionController@update');
    Route::get("/session/user-start-entry/{session}" , 'SessionController@userSessionStartEntry')->name('user.session.start_entry');
    Route::get("/session/user-end-entry/{session}" , 'SessionController@userSessionEndEntry')->name('user.session.end_entry');
    Route::resource('session', 'SessionController');
    Route::post('/session/reject' , 'SessionController@rejectSession')->name('session.reject');
    //wallet transation
    Route::resource('WalletTransaction', 'WalletTransactionController');
    Route::get('/wallet-update/{wallet}', 'WalletTransactionController@wallet_update')->name('wallet.update');
    //resume
    Route::get('/resume/filter' , [ResumeController::class,'filter']);
    Route::get('/resume/desktop' , [ResumeController::class,'desktop'])->name('resume.desktop');
    Route::get('/resume/desktop/filter' , [ResumeController::class,'filterDesktop']);
    Route::post('/resume-delete' , [ResumeController::class,'destroy']);
    Route::get('/recruitment-notice/filter', [RecruitmentNoticeController::class,'filter']);
    Route::resource('recruitmentNotice', RecruitmentNoticeController::class);
    Route::post('resume/transfer' , [ResumeController::class,'transfer']);
    Route::resource('resume', ResumeController::class);

    //agency
    Route::get('/agency/filter', 'AgencyController@filter');
    Route::get('/agency-suspend', 'AgencyController@suspend')->name('agency.suspend');
    Route::resource('agency', 'AgencyController');
    Route::resource('agencyType', 'AgencyTypeController');
    //contract
    Route::resource('contract', 'ContractController');
    Route::resource('arrangeContract', 'ArrangeContractController');
    Route::get('/arrangeContract/{id}/{status}', 'ArrangeContractController@status');
    Route::get('/arrangeContractList/{contract_id}', 'ArrangeContractController@arrangeContractList');
    //sms
    Route::get('/sms/single/create', 'GroupsmsController@single')->name('sms.single');
    Route::get('/sms/single/edit/{groupsms}', 'GroupsmsController@singleEdit')->name('sms.edit');
    Route::post('/sms/single/store', 'GroupsmsController@singleStore')->name('sms.single.store');
    Route::post('/sms/sendConsent', 'GroupsmsController@sendConsent')->name('sms.sendConsent');
    Route::post('/groupsms/phone/store', 'GroupsmsController@groupsmsPhone')->name('groupsms.phone.store');
    Route::patch('/groupsms/phone/update/{groupsms}', 'GroupsmsController@groupsmsPhoneUpdate')->name('groupsms.phone.update');
    Route::resource('groupsms', 'GroupsmsController');

    // Ai
// AI Settings (باید قبل از {id} باشه)
    Route::get('/chat-messages/setting', [AiController::class, 'setting'])->name('ai_settings.setting');
    Route::put('/chat-messages/setting/{id}', [AiController::class, 'updateSetting'])->name('ai_settings.update');

// Chat Messages
    Route::get('/chat-messages', [AiController::class, 'index'])->name('chat_messages.index');
    Route::get('/chat-messages/{id}', [AiController::class, 'show'])->name('chat_messages.show');

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
    Route::get('/contact/desktop', 'ContactController@desktop')->name('contact.desktop');
    Route::resource('contact', 'ContactController');

    /*
     * The standalone gallery feature (Admin\GalleryController, Admin\GalleryCategoryController,
     * the GalleryCategory model and the public /gallery page) was deleted: it was dead in every
     * direction — its views (Admin.gallery.*, Admin.gallerycategory.*) no longer existed and the
     * `gallery_categories` table it queried does not exist in the database, so every route 500'd.
     * Nothing linked to any of it.
     *
     * The one action that still ran was Admin\GalleryController@destroy, reached by a GET route
     * (CSRF-exempt by nature), which deleted a legacy `galleries` row and its file — exactly the
     * data touch-to-migrate reads.
     *
     * The `galleries` TABLE itself is untouched and still in use: it is the legacy source that
     * AdoptsLegacyMedia migrates into Spatie collections.
     */
    //content site
    Route::resource('about', 'AboutController')->except(['create', 'store']);
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
Route::get('/help', 'HelpController@index')->name('site.help');
Route::get('/calculate', 'FactorItemInstallmentController@index')->name('site.help');
Route::post('dashboard/financialPlansType/setByItem', 'Admin\FinancialPlansTypeController@setFinancialPlansTypeByItem');
Route::post('dashboard/serviceItem/setByItem', 'Admin\ServiceItemController@selectByService');
Route::get('/link/{callcenter}', 'HomeController@index');
Route::get('/message1403', 'HomeController@message');
Route::get('/mobile', 'HomeController@mobile')->name('mobile');
Route::get('/mobile/link/{callcenter}', 'HomeController@mobile');
Route::post('/newsletter', 'HomeController@newsletter')->name('newsletter');
Route::post('/phone', 'HomeController@phone')->name('phone');
Route::post('/service/financialPlansType', 'ServiceController@getFinancialPlansType');
Route::post('/service/item', [\App\Http\Controllers\ServiceController::class,'getServiceItem']);
Route::get('/services', 'ServiceController@index')->name('services');
Route::get('/service-items/{id}', [ServiceController::class, 'getItems']);
Route::get('/services/{service}', 'ServiceController@single')->name('services.show');
Route::get('/serviceItem/{serviceItem}', 'ServiceController@serviceItemSingle');
Route::get('/services/{service}/{callcenter}', 'ServiceController@single');
Route::get('/services/{service}/{callcenter}/{task}', 'ServiceController@single');
Route::get('/services/{service}/{callcenter}/{task}/{factor}', 'ServiceController@single');
Route::get('/recruitment-notice', 'RecruitmentNoticeController@index');
Route::get('/Permanent-employment', 'RecruitmentNoticeController@index');
Route::get('/challenge', 'HomeController@challenge');
Route::get('/contact', 'ContactController@index');
Route::post('/registerBasicInformation', 'RecruitmentNoticeController@registerBasicInformation');
Route::post('/send_resume', 'RecruitmentNoticeController@send_resume');
Route::post('/savecontact', 'ContactController@savecontact');
Route::get('/about', 'AboutController@index');
Route::get('/partner', 'PartnerController@partner');
Route::get('/agency', 'AgencyController@index')->name('agency');
Route::post('/agency', 'AgencyController@store');
Route::get('/centerRequest/create', 'CenterRequestController@create');
Route::get('/centerCampaign', 'CenterRequestController@centerCampaign');
Route::post('/centerRequest/store', 'CenterRequestController@store');
Route::get('/agencies', 'AgencyController@index')->name('agencies');
Route::get('/articles', 'ArticleController@articles');
Route::get('/articles/{articleSlug}', 'ArticleController@single');
Route::get('/agencies/{service}', 'AgencyController@single');
/*
 * `/gallery` (App\Http\Controllers\GalleryController) deleted with its views: the page
 * 500'd on every request. It called GalleryCategory::get(), but the `gallery_categories`
 * table does not exist in this database, and site/gallerydata.blade.php read
 * $gallery->cate_id, which is not a column on `galleries` either. Nothing linked to it.
 */
Route::get('/rules', 'RulesController@index');
Route::post('/rate', 'ArticleController@rate');
Route::get('/faq', 'HomeController@faq');
Route::get('/calculate', 'InstallmentController@showCalculate');

//Payment
Route::post('/pay', 'PaymentController@pay')->name('pay');
Route::post('/payPrepayment', 'PaymentController@payPrepayment')->name('payPrepayment');
// Route::get('/callback', 'PaymentController@callback')->name('pay.callback');
Route::match(['get', 'post'], '/callback', [PaymentController::class,'callback'])->name('pay.callback');
Route::post('/wallet-payment', 'WalletController@payment')->name('wallet-payment');
Route::post('/wallet-payment/checker', 'WalletController@paymentchecker')->name('wallet-payment.checker');
//employee
Route::get('/employeeRegister', 'EmployeeController@showRegistrationForm')->name('employee.register');
Route::post('/employeeRegister', 'EmployeeController@register');
Route::get('/employeeRegister/check-user-is-register','EmployeeController@checkUserIsRegister');

Route::get('/checkout/{factor}', 'CheckoutController@index')->name('checkout');
Route::get('/loan', action: 'FinancialPlansTypeController@index');
//Factor
Route::middleware('auth')->group(function () {

    //Checkout
    Route::post('/checkout', 'CheckoutController@store')->name('checkout.store');
    Route::post('/upload_document', 'CheckoutController@upload_document')->name('checkout.upload');
    Route::post('/factorItem/request-contract-settlement', 'FactorItemController@requestContractSettlement')->name('site.factorItem.requestContractSettlement');
    Route::post('/factorItem/save-recommendations', 'FactorItemController@saveRecommendations')->name('site.factorItem.saveRecommendations');
    Route::get('/factor/create', 'FactorController@create')->name('factor.create');
    Route::get('/cart', 'CartController@index')->name('cart');
    Route::post('/addToCart', 'CartController@add');
    Route::post('/deleteCart', 'CartController@delete');
    Route::post('/discount', 'CartController@discount')->name('discount');
    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::get('/profile/detail', 'ProfileController@detail')->name('profile.detail');
    Route::get('/profile/service', 'ProfileController@service')->name('profile.service');
    Route::get('/profile/wallet', 'ProfileController@wallet')->name('profile.wallet');
    Route::get('/profile/factor', 'ProfileController@factor')->name('profile.factor');
    Route::get('/profile/factorItem', 'ProfileController@factorItem')->name('profile.factorItem');
    Route::get('/profile/laleCard', 'ProfileController@laleCard')->name('profile.laleCard');
    Route::get('/profile/notifications', 'ProfileController@notifications')->name('profile.notifications');
    Route::get('/profile/notifications/read', 'ProfileController@notifications_read')->name('profile.notifications.read');
    Route::post('/profile', 'ProfileController@update')->name('profile.update');
    Route::get('/ghorfeCatego/{ghorfeId}', [GhorfeOnlineListController::class,'getGhofeCategories'])->name('ghorfeCatego');
    Route::get('/ghorfeCategoyDetail/{categoryId}', [GhorfeOnlineListController::class,'ghofeCategoryDetail'])->name('ghorfeCategoryDetail');
    Route::post('/useravatar', 'ProfileController@useravatar')->name('useravatar');
});

// Public temporary upload endpoint (used by the .file_upload_ajax / multipleImages widgets on
// both dashboard and guest-facing site forms). Throttled to mitigate abuse; the controller
// validates file type/size server-side.
Route::post('/dashboard/uploadFilesTemporarily', 'Admin\AdminController@uploadFilesTemporarily')
    ->middleware('throttle:60,1')
    ->name('uploadFilesTemporarily');

// Public inline view of a just-uploaded TEMP file (guest site forms have no dashboard auth).
// Scoped in the controller to temp holders only, so it can never serve a model's saved media.
Route::get('/upload/view/{id}', 'Admin\AdminController@viewTempMedia')
    ->middleware('throttle:120,1')
    ->name('viewTempMedia');

// Public removal of a just-uploaded TEMP file (guest site forms). Scoped in the
// controller to temp holders only, so it can never delete a model's saved media.
Route::post('/upload/removeTemp', 'Admin\AdminController@removeTempMedia')
    ->middleware('throttle:60,1')
    ->name('removeTempMedia');

Route::get('/factor/{factor}', 'FactorController@index')->name('factor');
Route::get('/factorItem/{factorItem}', 'FactorItemController@show');
Route::get('/factorItem/factor/{factorItem}', 'FactorItemController@showFactor');
Route::post('/factorItem/sendConfirmContractCode', 'FactorItemController@sendConfirmContractCode');
Route::post('/factorItem/verificationConfirmContractCode', 'FactorItemController@verificationConfirmContractCode');
Route::post('/factorItem/verificationConfirmContractCodeFactor', 'FactorItemController@verificationConfirmContractCodeFactor');
Route::post('/factorItem/verificationConfirmContractCodeEmployee', 'FactorItemController@verificationConfirmContractCodeEmployee');
Route::post('/factorItem/rejectFactor/{factorItem}', 'FactorItemController@rejectFactor')->name('factorItem.rejectFactor');
Route::get('/payment/{payment}', 'FactorItemController@index')->name('payment');
Route::get('/pf/{factor}', 'PaymentController@fastpay')->name('pay.fast');
Route::get('/pp/{payment}', 'PaymentController@fastPrePay')->name('pay.prepayment');
Route::post('/coupon', 'FactorController@coupon')->name('coupon');
Route::post('/user/sellingAccount', 'CampaignController@sellingAccount')->name('user.sellingAccount');

//singleWebPage
Route::get('/singleWebPage/{singleWebPage}', 'SingleWebPageController@index');
Route::get('/singleWebPage/{singleWebPage}/{service}', 'SingleWebPageController@single');
Route::get('/singleWebPage/checkout/{singleWebPage}/{factor}', 'SingleWebPageController@checkout');
//organization & representation
Route::get('/{representation}/serviceItem/{serviceItem}', 'RepresentationController@serviceItemSingle');
Route::get('/{representation}/serviceItem/{serviceItem}/{callcenter}', 'RepresentationController@serviceItemSingle');Route::get('/{organization}/services/{service}', 'OrganizationController@single');
Route::get('/organizations/{organization}', 'OrganizationController@index');
Route::get('/{representation}/Permanent-employment', 'RecruitmentNoticeController@index');
Route::get('/{representation}', 'RepresentationController@index');
Route::get('/{representation}/link/{callcenter}', 'RepresentationController@index');
Route::get('/{representation}/{service}', 'RepresentationController@single');
Route::get('/{representation}/{service}/{callcenter}', 'RepresentationController@single');
//Front Ajax
Route::post('/city/select', 'HomeController@cityselect');

Route::post('/dashboard/installment/calculate', 'Admin\TotalFactorItemInstallmentController@calculate');
