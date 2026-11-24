<?php

namespace Adm\Crud\Field;

use App\Models\File;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class Wysiwyg extends Field
{
    /**
     * Returns a JSON response containing details about the
     * requested file, ready for use within the TinyMCE file
     * modal
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \InvalidArgumentException
     */
    public function getFileDetails(): JsonResponse
    {
        $file = request()->input('file');
        if (!$file) {
            throw new InvalidArgumentException('Request missing file.');
        }

        $SelectedFile = File::find($file);
        if (!$SelectedFile) {
            throw new InvalidArgumentException('File not found.');
        }

        return response()->json([
            'src'       => $SelectedFile->is_image ? route('image', $SelectedFile->full_path) : route('file', $SelectedFile->full_path),
            'alt'       => $SelectedFile->alt_text,
            'width'     => $SelectedFile->width,
            'height'    => $SelectedFile->height
        ]);
    }
}
