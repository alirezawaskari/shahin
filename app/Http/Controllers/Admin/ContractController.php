<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ContractMediaException;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractFolder;
use App\Models\ContractHistory;
use App\Models\ContractUsage;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Services\V1\Admin\ContractMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
 {
    public function __construct( protected ContractMediaService $media )
    {
    }

    /** Up to 5 staff files per contract, each checked by the same rules the media service enforces. */
    private function staffFileRules(): array
    {
        return [
            'staff_files' => 'nullable|array|max:' . ContractMediaService::STAFF_LIMIT,
            'staff_files.*' => ContractMediaService::fileRules(),
        ];
    }

    private function staffFileMessages(): array
    {
        return [
            'staff_files.max' => 'حداکثر ۵ فایل مدیریت مجاز است.',
            'staff_files.*.mimes' => 'فرمت فایل‌ها باید pdf, doc, docx, jpg, jpeg یا png باشد.',
            'staff_files.*.max' => 'حجم هر فایل نباید بیش از ۱۰ مگابایت باشد.',
        ];
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function index( Request $request )
 {
        $contracts = Contract::with( 'folder' )->withCount( 'usages' )->orderBy( 'id', 'DESC' );
        $queries = [];
        $columns = [ 'status', 'type' ];
        foreach ( $columns as $column ) {
            if ( $request->has( $column ) ) {
                if ( $request->$column != 'all' ) {
                    $contracts = $contracts->where( $column, $request->$column );
                    $queries[ $column ] = $request->$column;
                }
                if ( $request->$column == 'all' ) {
                    $queries[ $column ] = 'all';
                }
            }
        }

        // 'none' is «دسته‌بندی نشده» — contracts that sit outside every folder.
        if ( $request->has( 'contract_folder_id' ) && $request->contract_folder_id != 'all' ) {
            if ( $request->contract_folder_id === 'none' ) {
                $contracts = $contracts->whereNull( 'contract_folder_id' );
            } else {
                $contracts = $contracts->where( 'contract_folder_id', $request->contract_folder_id );
            }
            $queries[ 'contract_folder_id' ] = $request->contract_folder_id;
        } elseif ( $request->has( 'contract_folder_id' ) ) {
            $queries[ 'contract_folder_id' ] = 'all';
        }

        $contracts = $contracts->paginate( 15 )->appends( $queries );
        $folders = ContractFolder::options();

        $statuses = [
            '1' => 'منتظر تایید وکیل', '2' => 'منتظر تایید مدیر مالی', '3' => 'منتظر تایید مدیر عامل'
        ];
        $type = [ '1' => 'خرید', '2' => 'فروش', '3' => 'واگذاری' ];

        return view( 'Admin.contract.lists', compact( 'statuses', 'contracts', 'type', 'queries', 'folders' ) );
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */

    public function create()
 {
        return view( 'Admin.contract.create', [ 'folders' => ContractFolder::options() ] );
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store( Request $request )
    {
        // The Word file is optional: a contract may be text-only, and the original
        // file is only offered for download when one was actually attached.
        $request->validate( array_merge( [ 'title' => 'required', 'type'=>'required', 'contract_folder_id' => 'nullable|integer|exists:contract_folders,id' ], $this->staffFileRules() ), array_merge( [
            'title.required' => 'لطفا عنوان را وارد کنید',
            'type.required' => 'لطفا نوع را انتخاب کنید',
            'contract_folder_id.exists' => 'پوشه انتخاب شده معتبر نیست',
        ], $this->staffFileMessages() ) );

        try {
            DB::transaction( function () use ( $request ) {
                // legacy media columns are never mass-assigned — see AdoptsLegacyMedia::exceptLegacyMediaKeys()
                $contract = Contract::create( ( new Contract )->exceptLegacyMediaKeys( $request->all() ) );

                if ( $request->file_path ) {
                    MediaLibraryHelper::attachTo( $contract, $request->file_path, 'file' );
                }

                $this->media->addStaffFiles( $contract, $request->file( 'staff_files', [] ) );

                ContractHistory::create( [ 'contract_id'=>$contract->id, 'user_id'=>\Auth::user()->id, 'file'=>$contract->file ] );
            } );
        } catch ( ContractMediaException $e ) {
            return redirect()->back()->withInput()->withErrors( [ 'staff_files' => $e->getMessage() ] );
        }

        return redirect( 'dashboard/contract' )->with( [ 'create' => 'success' ] );
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function show( $id )
    {
        $contract_histories = Contract::find( $id )->contractHistories()->paginate( 15 );
        return view( 'Admin.contract.show', compact( 'contract_histories' ) );
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function edit( $id )
    {
        if ( $id ) {
            $contract = Contract::with( [ 'usages.usable', 'folder' ] )->find( $id );
            return view( 'Admin.contract.edit', [
                'contract' => $contract,
                'folders' => ContractFolder::options(),
                'staffLocked' => $this->media->isStaffLocked( $contract ),
            ] );
        }
        return redirect()->back();
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function update( Request $request, $id )
    {
        $request->validate( array_merge( [ 'title' => 'required', 'type'=>'required' ], $this->staffFileRules() ), array_merge( [
            'title.required' => 'لطفا عنوان را وارد کنید',
            'type.required' => 'لطفا نوع را انتخاب کنید',
        ], $this->staffFileMessages() ) );
        $contract = Contract::find( $id );

        try {
            DB::transaction( function () use ( $request, $contract ) {
                $contract->update( $contract->exceptLegacyMediaKeys( $request->all() ) );

                if ( $request->file_path ) {
                    MediaLibraryHelper::attachTo( $contract, $request->file_path, 'file' );

                    ContractHistory::create( [ 'contract_id'=>$contract->id, 'user_id'=>\Auth::user()->id, 'file'=>$contract->file ] );
                }

                $this->media->addStaffFiles( $contract, $request->file( 'staff_files', [] ) );
            } );
        } catch ( ContractMediaException $e ) {
            return redirect()->back()->withInput()->withErrors( [ 'staff_files' => $e->getMessage() ] );
        }

        return redirect( '/dashboard/contract' )->with( [ 'update' => 'success' ] );
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function destroy( $id )
    {
        if ( $id ) {
            $contract = Contract::find( $id );
            $contract->delete();
        }
        return redirect()->back();
    }

    /**
     * خروجی قرارداد — the printable/PDF export.
     *
     * The contract text and the parties are always rendered; the service, invoice
     * and employee blocks appear only when the caller supplies that context, which
     * is what «if applicable» in the spec means. Context arrives as query params so
     * the same export serves all three display points.
     */
    public function export( Request $request, $id, $usage = null )
    {
        $contract = Contract::with( 'folder' )->findOrFail( $id );

        $contractUsage = $usage
            ? ContractUsage::with( 'usable' )->where( 'contract_id', $contract->id )->find( $usage )
            : null;

        $factorItem = $request->filled( 'factor_item_id' )
            ? FactorItem::with( [ 'factor.user', 'factoritemable' ] )->find( $request->factor_item_id )
            : null;

        $factor = $request->filled( 'factor_id' )
            ? Factor::with( 'user' )->find( $request->factor_id )
            : optional( $factorItem )->factor;

        $employee = $request->filled( 'employee_id' )
            ? Employee::with( 'user' )->find( $request->employee_id )
            : null;

        // The service the contract is about: the usage owner when it is a service,
        // otherwise the service the invoice line was registered for.
        $service = $contractUsage && $contractUsage->usable_type === \App\Models\Service::class
            ? $contractUsage->usable
            : ( $factorItem && $factorItem->factoritemable_type === \App\Models\Service::class
                ? $factorItem->factoritemable
                : null );

        // The counterparty (طرف دوم قرارداد): the employee's user, else the invoice owner.
        $counterparty = optional( $employee )->user ?: optional( $factor )->user;

        return view( 'Admin.contract.export', compact(
            'contract', 'contractUsage', 'factor', 'factorItem', 'employee', 'service', 'counterparty'
        ) );
    }

    /**
     * Streams the original contract file (Word/PDF) when one is attached.
     */
    public function download( $id )
    {
        $contract = Contract::findOrFail( $id );

        $media = $contract->getFirstMedia( 'file' );

        if ( $media ) {
            return response()->download( $media->getPath(), $media->file_name );
        }

        // Legacy contracts still point at a public path in the `file` column.
        $legacy = $contract->getRawOriginal( 'file' );

        if ( $legacy && file_exists( public_path( $legacy ) ) ) {
            return response()->download( public_path( $legacy ) );
        }

        return redirect()->back()->withErrors( [ 'file' => 'فایلی برای این قرارداد بارگذاری نشده است' ] );
    }

    /**
     * Adds staff files to a contract, up to 5 in total. Refused once the contract is locked.
     */
    public function storeStaffFiles( Request $request, $id )
    {
        $request->validate( $this->staffFileRules(), $this->staffFileMessages() );

        $contract = Contract::findOrFail( $id );

        try {
            $this->media->addStaffFiles( $contract, $request->file( 'staff_files', [] ) );
        } catch ( ContractMediaException $e ) {
            return redirect()->back()->withErrors( [ 'staff_files' => $e->getMessage() ] );
        }

        return redirect()->back()->with( [ 'update' => 'success' ] );
    }

    /**
     * Removes one staff file. Refused once the contract is locked.
     */
    public function destroyStaffFile( $id, $mediaId )
    {
        $contract = Contract::findOrFail( $id );

        try {
            $this->media->removeStaffFile( $contract, (string) $mediaId );
        } catch ( ContractMediaException $e ) {
            return redirect()->back()->withErrors( [ 'staff_files' => $e->getMessage() ] );
        }

        return redirect()->back()->with( [ 'update' => 'success' ] );
    }
}
