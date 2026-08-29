<?php

namespace Tests\Unit;

use App\Http\Requests\Admin\BusinessTransferRequest;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class BusinessTransferRequestTest extends TestCase
{
    public function test_authorization_requires_the_independent_transfer_permission(): void
    {
        foreach ([false, true] as $allowed) {
            $user = $this->createMock(User::class);
            $user->expects($this->once())
                ->method('hasPermission')
                ->with('transfer_to_business')
                ->willReturn($allowed);

            $request = new BusinessTransferRequest;
            $request->setUserResolver(fn () => $user);

            $this->assertSame($allowed, $request->authorize());
        }
    }
}
