<?php

namespace Tests\Unit;

use App\Models\Factor;
use App\Models\Request;
use App\Models\User;
use App\Services\Sms\DynamicParameterResolver;
use Tests\TestCase;

class DynamicParameterResolverTest extends TestCase
{
    public function test_resolves_user_name_and_family(): void
    {
        $user = new User();
        $user->name = 'علی';
        $user->family = 'رضایی';

        $resolver = new DynamicParameterResolver();
        $template = 'سلام {نام کاربر} {نام خانوادگی} عزیز';
        $resolved = $resolver->applyTo($template, ['user' => $user]);

        $this->assertEquals('سلام علی رضایی عزیز', $resolved);
    }

    public function test_resolves_factor_id_and_payment_link(): void
    {
        $factor = new Factor();
        $factor->id = 12345;
        $factor->resnumber = 'FAC-9988';

        $resolver = new DynamicParameterResolver();
        $template = 'فاکتور شما به شناسه {شناسه فاکتور} صادر شد. پرداخت: {لینک پرداخت}';
        $resolved = $resolver->applyTo($template, ['factor' => $factor]);

        $this->assertStringContainsString('12345', $resolved);
        $this->assertStringContainsString('lalecard.ir/pf/FAC-9988', $resolved);
    }

    public function test_resolves_request_id(): void
    {
        $request = new Request();
        $request->id = 789;

        $resolver = new DynamicParameterResolver();
        $template = 'درخواست شما با شماره پیگیری {شناسه درخواست} ثبت گردید.';
        $resolved = $resolver->applyTo($template, ['request' => $request]);

        $this->assertEquals('درخواست شما با شماره پیگیری 789 ثبت گردید.', $resolved);
    }

    public function test_resolves_date_and_time(): void
    {
        $resolver = new DynamicParameterResolver();
        $template = 'جلسه در تاریخ {تاریخ} ساعت {ساعت} برگزار خواهد شد.';

        // With explicit contextual date/time
        $resolved = $resolver->applyTo($template, [
            'date' => '1405/01/15',
            'time' => '10:30',
        ]);

        $this->assertEquals('جلسه در تاریخ 1405/01/15 ساعت 10:30 برگزار خواهد شد.', $resolved);

        // With default automatic date/time
        $autoResolved = $resolver->applyTo($template);
        $this->assertStringNotContainsString('{تاریخ}', $autoResolved);
        $this->assertStringNotContainsString('{ساعت}', $autoResolved);
    }

    public function test_extensible_with_custom_closures(): void
    {
        $resolver = new DynamicParameterResolver();
        $resolver->register('{کد رهگیری}', function (array $context) {
            return $context['tracking_code'] ?? 'TRK-000';
        });

        $template = 'کد شما: {کد رهگیری}';
        $resolved = $resolver->applyTo($template, ['tracking_code' => 'TRK-5544']);

        $this->assertEquals('کد شما: TRK-5544', $resolved);
    }

    public function test_content_without_placeholders_remains_untouched(): void
    {
        $resolver = new DynamicParameterResolver();
        $plain = 'متن پیامک ساده بدون هیچ متغیر';
        $this->assertEquals($plain, $resolver->applyTo($plain));
    }
}
