<?php

namespace App\Services;

use App\Models\Session;

class SessionService
{
    public function createSessionPendingMainInvoice($factorItemId)
    {
        $session = Session::create([
            'status' => 5,
            'user_id' => auth()->user() ? auth()->user()->id : null,
            'factor_item_id' => $factorItemId,
            'date' => now(),
            'description_before' => 'این پرونده آماده اجرا جهت شروع کار می باشد.',
        ]);

        return $session;
    }
}
