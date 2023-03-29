<?php

namespace App\Imports;

use App\Phone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PhoneImport implements ToModel , WithHeadingRow ,WithChunkReading, ShouldQueue
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        return new Phone([
            'phone' => $row['number'],
            'phone_list_id' => $this->id
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
