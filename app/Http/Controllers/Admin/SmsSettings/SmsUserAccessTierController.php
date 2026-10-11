<?php

namespace App\Http\Controllers\Admin\SmsSettings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsUserAccessTierController extends Controller
{
    public function __construct(
        protected SmsAccessTierService $accessTierService
    ) {
    }

    /**
     * Display a listing of system users and their SMS access tiers.
     * Accessible only by SuperAdmin.
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'تنها مدیر ارشد سامانه (SuperAdmin) به این بخش دسترسی دارد.');

        $query = User::query()
            ->with(['employee.ghorfeOnline', 'roles'])
            ->whereNotNull('phone');

        // Search by name or phone
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('family', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by tier
        if ($tier = $request->input('tier')) {
            if ($tier !== 'all') {
                $query->where('access_tier', $tier);
            }
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();

        $tierStats = [
            'total' => User::whereNotNull('phone')->count(),
            'silver' => User::where('access_tier', SmsAccessTierService::TIER_SILVER)->count(),
            'gold' => User::where('access_tier', SmsAccessTierService::TIER_GOLD)->count(),
            'diamond' => User::where('access_tier', SmsAccessTierService::TIER_DIAMOND)->count(),
            'titanium' => User::where('access_tier', SmsAccessTierService::TIER_TITANIUM)->count(),
        ];

        return view('Admin.sms.settings.access_tiers.index', compact('users', 'tierStats'));
    }

    /**
     * Update the SMS access tier for a specific user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'تنها مدیر ارشد سامانه مجاز به تغییر سطح دسترسی کاربران است.');

        $request->validate([
            'access_tier' => 'required|in:silver,gold,diamond,titanium',
        ]);

        $newTier = strtolower($request->input('access_tier'));
        $user->access_tier = $newTier;
        $user->save();

        $tierLabels = [
            'silver' => 'نقره‌ای (Silver)',
            'gold' => 'طلایی (Gold)',
            'diamond' => 'الماس (Diamond)',
            'titanium' => 'تیتانیوم (Titanium)',
        ];

        $label = $tierLabels[$newTier] ?? $newTier;

        return redirect()->back()->with('success', "سطح دسترسی کاربر {$user->name} {$user->family} با موفقیت به {$label} ارتقا/تغییر یافت.");
    }
}
