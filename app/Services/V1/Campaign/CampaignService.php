<?php

namespace App\Services\V1\Campaign;

class CampaignService
{
    public function checkCampaign($Campaign)
    {
        if ($Campaign) {
            if ($Campaign->start_date && $Campaign->end_date) {
                if (now()->gte($Campaign->start_date) && now()->lte($Campaign->end_date)) {
                    if ($Campaign->usage && $Campaign->usage > 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
