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
            return ghorfe();
        } elseif ($user->representation) {
            return $user->representation;
        } else {
            return $user;
        }
    }
}
