<?php

namespace App\Services\Sms;

use App\Models\Center;
use App\Models\GhorfeOnlineList;
use App\Models\Representation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the sender information ({company_name}, {phone}, {address}) that is
 * injected into SMS templates, based on the role (level) of the user who is
 * sending the message:
 *
 *  - ghorfeAdmin ................ shop info (GhorfeOnlineList + its ghorfe_settings)
 *  - admin / superAdmin with
 *    admin_representation_id or
 *    center_id filled ........... representation info (Representation + its ghorfe_settings)
 *  - superAdmin (no rep/center) . main company info (own settings, then config('company'))
 *  - no authenticated user ...... main company info (cron jobs, queued jobs)
 *
 * Only placeholders that actually appear in a template are replaced, so a
 * template using a single placeholder is handled the same as one using all.
 */
class SmsSenderInfoResolver
{
    /** Placeholder tokens supported inside SMS templates. */
    private const PLACEHOLDERS = [
        'company_name' => '{company_name}',
        'phone'        => '{phone}',
        'address'      => '{address}',
    ];

    /**
     * Replace the sender-info placeholders in a template with the values that
     * match the currently authenticated sender's role.
     */
    public function applyTo(string $content): string
    {
        // Nothing to do when the template carries no sender placeholder.
        if (!str_contains($content, '{')) {
            return $content;
        }

        $info = $this->resolve();

        return str_replace(
            array_values(self::PLACEHOLDERS),
            [$info['company_name'], $info['phone'], $info['address']],
            $content
        );
    }

    /**
     * @return array{company_name: string, phone: string, address: string}
     */
    public function resolve(): array
    {
        $user = Auth::user();

        if ($user instanceof User) {
            if ($user->level === 'ghorfeAdmin') {
                return $this->fromGhorfe($user);
            }

            if (in_array($user->level, ['admin', 'superAdmin'], true)
                && ($user->admin_representation_id || $user->center_id)) {
                $representation = $this->representationFor($user);
                if ($representation) {
                    return $this->fromRepresentation($representation);
                }
            }
        }

        // superAdmin with no shop/representation, or no authenticated user.
        return $this->mainCompany($user instanceof User ? $user : null);
    }

    /**
     * Shop information for a ghorfeAdmin. Company name is the shop title;
     * phone/address are read from the shop's ghorfe_settings rows.
     */
    private function fromGhorfe(User $user): array
    {
        $ghorfe = ghorfe();

        if (!$ghorfe) {
            return $this->mainCompany($user);
        }

        $settings = $this->settingsOf($ghorfe);

        return [
            'company_name' => $ghorfe->title ?? config('company.name'),
            'phone'        => $settings['phone'] ?? '',
            'address'      => $settings['address'] ?? '',
        ];
    }

    /**
     * Representation information. Prefers the representation's ghorfe_settings
     * rows, falling back to the columns on the representation itself (which is
     * where representation contact data actually lives today).
     */
    private function fromRepresentation(Representation $representation): array
    {
        $settings = $this->settingsOf($representation);

        return [
            'company_name' => $representation->fa_name
                ?: ($representation->en_name ?: config('company.name')),
            'phone'        => $settings['phone']
                ?? ($representation->call ?: ($representation->call_number ?: '')),
            'address'      => $settings['address']
                ?? ($representation->address ?: ''),
        ];
    }

    /**
     * Main company (Lale) info: the acting super admin's own settings when
     * present, otherwise the config defaults.
     */
    private function mainCompany(?User $user): array
    {
        $settings = $user ? $this->settingsOf($user) : [];

        return [
            'company_name' => config('company.name'),
            'phone'        => $settings['phone'] ?? config('company.phone'),
            'address'      => $settings['address'] ?? config('company.address'),
        ];
    }

    /**
     * Resolve the representation for an admin/superAdmin colleague. Prefers the
     * explicit admin_representation_id, then falls back to the representation
     * that created the user's center.
     */
    private function representationFor(User $user): ?Representation
    {
        if ($user->admin_representation_id) {
            return Representation::find($user->admin_representation_id);
        }

        if ($user->center_id) {
            $center = Center::find($user->center_id);
            if ($center) {
                return $center->creatorRepresentation
                    ?? $center->representations()->first();
            }
        }

        return null;
    }

    /**
     * key => value map of an owner's ghorfe_settings rows.
     *
     * @param GhorfeOnlineList|Representation|User $owner
     */
    private function settingsOf($owner): array
    {
        return $owner->settings()
            ->whereIn('key', ['phone', 'address'])
            ->pluck('value', 'key')
            ->toArray();
    }
}
