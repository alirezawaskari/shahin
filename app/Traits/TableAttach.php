<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

Trait TableAttach
{

    # create update table attach
    public function createUpdateTableAttach($disk, $directory, $inputName, $tableField)
    {
        # check file
        if (!request()->hasFile($inputName)) {
            return;
        }

        # set variables
        $file = request()->file($inputName);
        $fileName = $file->getClientOriginalName();
        $storage = Storage::disk($disk);
        $oldFilePath = "{$directory}/{$this->$tableField}";
        $newFilePath = "{$directory}/{$fileName}";

        # check duplicate for new file name
        if ($storage->exists($newFilePath)) {
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '-' . rand(1, 99) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        }

        # remove old file
        if (!empty($this->$tableField) && $storage->exists($oldFilePath)) {
            $storage->delete($oldFilePath);
        }

        # upload
        $file->storeAs($directory, $fileName, $disk);

        # DB
        $this->$tableField = $fileName;
        $this->save();
    }

    public function getTableAttach($disk, $directory, $tableField, $default)
    {
        $output = ["has" => false, "url" => ""];
        $storage = Storage::disk($disk);
        $filePath = "{$directory}/{$this->$tableField}";

        if (!empty($this->$tableField) && $storage->exists($filePath)) {
            $output['url'] = $storage->url($filePath);
            $output['has'] = true;
        } elseif (!empty($default)) {
            $output['url'] = $default;
        }

        return $output;
    }

    public function deleteTableAttach($disk, $directory, $tableField)
    {
        $storage = Storage::disk($disk);
        $filePath = "{$directory}/{$this->$tableField}";

        if (!empty($this->$tableField) && $storage->exists($filePath)) {
            $storage->delete($filePath);

            $this->$tableField = NULL;
            $this->save();
        }

        if (empty($storage->files($directory))) {
            $storage->deleteDirectory($directory);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            foreach (static::$tableAttachFields as $attachField) {
                $dir = static::$tableAttachDirectory . "/" . $model->id ;
                $model->deleteTableAttach(static::$tableAttachDisk, $dir, $attachField);
            }
        });

    }
}