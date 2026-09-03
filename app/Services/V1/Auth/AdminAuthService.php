<?php
declare( strict_types = 1 );

namespace App\Services\V1\Auth;

use App\DTOs\Auth\OtpVerificationDTO;
use App\DTOs\Auth\PasswordCredentialsDTO;
use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidAdminCredentialsException;
use App\Exceptions\Auth\NotAdminException;
use App\Exceptions\Auth\OtpNotificationException;
use App\Exceptions\Auth\TooManyOtpAttemptsException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Models\GhorfeOnlineList;
use App\Models\GhorfeOnlineListUser;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;

final readonly class AdminAuthService {
    public function __construct(
        private PasswordAuthenticator $passwordAuthenticator,
        private OtpService            $otpService,
        private StatefulGuard         $guard,
    ) {
    }

    /**
    * @throws InvalidAdminCredentialsException
    * @throws InactiveAccountException
    * @throws NotAdminException
    */

    public function loginByPassword( PasswordCredentialsDTO $credentials ): User {
        $user = $this->passwordAuthenticator->authenticate( $credentials );

        $this->guard->login( $user );

        return $user;

    }

    /**
    * @throws NotAdminException
    * @throws OtpNotificationException
    * @throws TooManyOtpRequestsException
    */

    public function requestOtp( string $phone ): int {
        return $this->otpService->sendCodeFor( $phone );
    }

    /**
    * @throws TooManyOtpAttemptsException
    * @throws NotAdminException
    */

    public function loginByOtp( OtpVerificationDTO $verification ): User {
        $user = $this->otpService->verifyCode(
            $verification->phone,
            $verification->code
        );

        $this->guard->login( $user );

        return $user;
    }

    /**
    * Summary of checkUserGhorfe
    * @return bool
    */
    public static function checkUserGhorfe() {
        $ghorfe_online_list = self::getGhorfe();
        if ( !$ghorfe_online_list )return false;
        // $ghorfe_online_list_user_exist = GhorfeOnlineListUser::where( 'user_id', auth()->user()->id )
        // ->where( 'ghorfe_online_list_id', $ghorfe_online_list->id )
        // ->exists();

        return true;
    }
    /**
    * Summary of getGhorfe
    * @return GhorfeOnlineList|null
    */
    public static function getGhorfe() : GhorfeOnlineList| null {
        $ghorfe_online_list = auth()->user()->employee->ghorfeOnline;
        // $ghorfe_domain_active = GhorfeOnlineList::getActiveDomainName();
        // $GhorfeOnlineList = GhorfeOnlineList::where( 'domain_active', $ghorfe_domain_active )->first();
        return $ghorfe_online_list ?? null;
    }
    public function logout(): void {
        $this->guard->logout();
    }
}
