<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Reads every sheet as a plain array of rows; the caller decides what the cells mean.
 * Used with Excel::toArray(), which never persists anything.
 */
class RawSheetImport implements ToArray
{
    public function array(array $array): void
    {
        // Nothing to do: Excel::toArray() collects the rows itself.
    }
}
