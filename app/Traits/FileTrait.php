<?php

    namespace App\Traits;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

    trait FileTrait {

        public function uploadImage(UploadedFile $uploadedFile, $folder = null, $disk = 'public', $filename = null)
        {
            $name = !is_null($filename) ? $filename : Str::random(25);
            $file = null;
            try {
                $file = $uploadedFile->storeAs($folder, $name.'.'.$uploadedFile->getClientOriginalExtension(), $disk);
            } catch (Exception $e) {
                throw new Exception('Failed Uploaded - ' . $e->getMessage());

            }
            return $file;
        }

        public function deleteImage($dir = null)
        {
            if(File::exists($dir)) {
                File::delete($dir);
            } else {
                throw new Exception("Error Delete Processing Request", 1);
            }
            return true;
        }

    }
