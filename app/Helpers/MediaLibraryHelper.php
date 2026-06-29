<?php

namespace App\Helpers;

use App\Models\Media;
use App\Models\MediaLibrary;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class MediaLibraryHelper {
    /**
    * @param UploadedFile $image
    * @param int $userId
    * @return MediaLibrary
    * @throws \RuntimeException
    */
    public static function uploadImage( UploadedFile $image, int $userId ): MediaLibrary {
        $filename = now()->format( 'Ymd_His' ) . '.' . strtolower( $image->getClientOriginalExtension() );

        $mediaLibrary = MediaLibrary::create( [
            'title'   => $filename,
            'alt'     => $filename,
            'user_id' => $userId,
        ] );

        try {
            $mediaLibrary->addMedia( $image )
            ->usingFileName( $filename )
            ->preservingOriginal()
            ->toMediaCollectionOnDisk( 'images', 'file_lalecard', now()->format('Y/m/d') );
        } catch ( \Exception $e ) {
            $mediaLibrary->delete();
            throw new \RuntimeException( 'Failed to upload image: ' . $e->getMessage() );
        }

        return $mediaLibrary;
    }
    /**
     * Summary of moveImage
     * @param string $fileUrl
     * @param int $userId
     * @throws \RuntimeException
     * @return MediaLibrary
     */
    public static function moveImage( string $fileUrl, int $userId ): MediaLibrary {
        $filename = pathinfo( parse_url( $fileUrl, PHP_URL_PATH ), PATHINFO_BASENAME );

        $mediaLibrary = MediaLibrary::create( [
            'title'   => $filename,
            'alt'     => $filename,
            'user_id' => $userId,
        ] );

        try {
            $mediaLibrary->addMedia( $fileUrl )
            ->usingFileName( $filename )
            ->toMediaCollectionOnDisk( 'images', 'file_lalecard', now()->format('Y/m/d') );
        } catch ( \Exception $e ) {
            $mediaLibrary->delete();
            throw new \RuntimeException( 'Failed to move image: ' . $e->getMessage() );
        }

        return $mediaLibrary;
    }
}
