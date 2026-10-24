<?php

namespace Tests\Feature;

use App\Exceptions\ContractMediaException;
use App\Services\V1\Admin\ContractMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Upload limits of the contract and SMS media forms: the Persian message for a file PHP refused,
 * and the rule that a failed upload is refused, never silently dropped.
 *
 * No database is touched: these are validation rules and the media service's file checks only.
 */
class SmsUploadLimitsTest extends TestCase
{
    /** What PHP hands over for a file larger than upload_max_filesize: an entry with error 1 and no content. */
    private function failedUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, '');

        return new UploadedFile($path, 'big.pdf', 'application/pdf', UPLOAD_ERR_INI_SIZE, true);
    }

    public function test_a_failed_upload_gets_the_persian_message_with_the_server_limit(): void
    {
        $validator = Validator::make(
            ['staff_files' => [$this->failedUpload()]],
            ['staff_files.*' => ContractMediaService::fileRules()],
            ContractMediaService::uploadMessages(['staff_files']),
        );

        $this->assertTrue($validator->fails());
        $message = $validator->errors()->first('staff_files.0');
        $this->assertSame(ContractMediaService::uploadFailedMessage(), $message);
        $this->assertStringContainsString('بارگذاری فایل ناموفق بود؛ حجم فایل از حد مجاز سرور (', $message);
        $this->assertStringContainsString(ContractMediaService::serverFileLimitLabel(), $message);
        $this->assertStringNotContainsString('failed to upload', $message);
    }

    public function test_every_media_field_gets_the_uploaded_message(): void
    {
        $messages = ContractMediaService::uploadMessages(['staff_files', 'staff_attachments', 'client_attachments', 'replace']);

        foreach (['staff_files', 'staff_attachments', 'client_attachments', 'replace'] as $field) {
            $this->assertSame(ContractMediaService::uploadFailedMessage(), $messages[$field . '.*.uploaded']);
        }
    }

    public function test_media_service_refuses_a_failed_upload_instead_of_skipping_it(): void
    {
        try {
            app(ContractMediaService::class)->assertFile($this->failedUpload());
            $this->fail('A failed upload must not pass the media service.');
        } catch (ContractMediaException $e) {
            $this->assertSame('invalid', $e->reason);
            $this->assertSame(ContractMediaService::uploadFailedMessage(), $e->getMessage());
        }
    }

    public function test_non_file_entries_are_refused_and_an_empty_input_is_no_files(): void
    {
        $this->assertSame([], ContractMediaService::uploadedFilesOf(null));
        $this->assertSame([], ContractMediaService::uploadedFilesOf([]));

        $good = UploadedFile::fake()->create('ok.pdf', 10, 'application/pdf');
        $this->assertSame([$good], ContractMediaService::uploadedFilesOf([$good]));

        foreach ([['not a file', $good], 'not-an-array', [null]] as $input) {
            try {
                ContractMediaService::uploadedFilesOf($input);
                $this->fail('A non-file entry must be refused, not dropped.');
            } catch (ContractMediaException $e) {
                $this->assertSame('فایل ارسالی معتبر نیست.', $e->getMessage());
            }
        }
    }

    public function test_php_size_values_are_read_in_bytes(): void
    {
        $this->assertSame(2 * 1024 * 1024, ContractMediaService::iniBytes('2M'));
        $this->assertSame(256 * 1024 * 1024, ContractMediaService::iniBytes('256M'));
        $this->assertSame(1024 ** 3, ContractMediaService::iniBytes('1G'));
        $this->assertSame(512, ContractMediaService::iniBytes('512'));
        $this->assertSame(0, ContractMediaService::iniBytes(false));
    }

    public function test_the_server_limit_label_is_in_persian_digits_and_matches_the_ini_value(): void
    {
        $megabytes = intdiv(ContractMediaService::iniBytes(ini_get('upload_max_filesize')), 1024 * 1024);
        $expected = strtr((string) $megabytes, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);

        $this->assertSame($expected . ' مگابایت', ContractMediaService::serverFileLimitLabel());
    }

    public function test_picker_attributes_default_to_no_saved_files(): void
    {
        $attributes = ContractMediaService::pickerAttributes();

        $this->assertStringContainsString('data-max-files="5"', $attributes);
        $this->assertStringContainsString('data-existing-files="0"', $attributes);
    }

    public function test_picker_attributes_carry_the_saved_file_count_and_never_go_negative(): void
    {
        $this->assertStringContainsString('data-existing-files="3"', ContractMediaService::pickerAttributes(5, 3));
        $this->assertStringContainsString('data-existing-files="0"', ContractMediaService::pickerAttributes(5, -2));
    }
}
