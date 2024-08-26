<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllocationEmployeeShareCenter;

class AllocationEmployeeShareCenterController extends Controller {
    /**
    * @param \App\Models\AllocationEmployeeShareCenter $allocationEmployeeShareCenter
    * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse
    */

    public function destroy( AllocationEmployeeShareCenter $allocationEmployeeShareCenter ) {

        if ( auth()->user()->hasPermission( 'allocationEmployeeShareCenter_delete' ) ) {
            $allocationEmployeeShareCenter->delete();
            return response( [ 'status'=>'success' ] );
        }
        return response( [ 'status'=>'error' ] );
    }
}
