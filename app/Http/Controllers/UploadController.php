<?php

namespace App\Http\Controllers;

use App\Models\TemporaryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        if($request->hasFile('upload')){
            $file = $request->file('upload');
            $fileName = $file->getClientOriginalName();
            $folder = uniqid() . '-' . now()->timestamp;
            $file->storeAs('uploads/tmp/'. $folder, $fileName);

            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $fileName,
            ]);

            return json_encode(['folder' => $folder, 'filename' => $fileName]);
        }

        return 'Bad file';
    }

    public function destroy(Request $request)
    {
        $requestFile = json_decode($request->getContent(), true);

        $tmpFile = TemporaryFile::where('folder', $requestFile['folder'])
                                ->where('filename', $requestFile['filename'])
                                ->get();

        TemporaryFile::destroy($tmpFile);

        $delete = File::deleteDirectory(storage_path('app/uploads/tmp/' . $requestFile['folder']));

        return ($delete) ? 'Deleted' : 'Delete Failed';
    }
}
