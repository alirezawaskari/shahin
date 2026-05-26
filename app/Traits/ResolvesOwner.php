<?php

namespace App\Traits;
trait ResolvesOwner
{
    protected function getOwner()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        if ($user->level == 'ghorfeAdmin') {
            $host = request()->getHost();

            return $user->ghorfeOnlineLists()
                ->where('domain_active', $host)
                ->first();
        } elseif ($user->representation) {
            return $user->representation;
        } else {
            return $user;
        }
    }
}
