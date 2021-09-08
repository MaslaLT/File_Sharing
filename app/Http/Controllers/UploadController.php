<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Models\TemporaryFile;
use App\Models\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function uploadTmp(Request $request)
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

    public function destroyTmp(Request $request)
    {
        $requestFile = json_decode($request->getContent(), true);

        $tmpFile = TemporaryFile::where('folder', $requestFile['folder'])
                                ->where('filename', $requestFile['filename'])
                                ->get();

        TemporaryFile::destroy($tmpFile);

        $delete = File::deleteDirectory(storage_path('app/uploads/tmp/' . $requestFile['folder']));

        return ($delete) ? 'Deleted' : 'Delete Failed';
    }

    public function store(StoreFileRequest $request)
    {
        $string = implode("", range('1 ', 'z'));
        $password = substr(str_shuffle($string),'0', 10);
        $json = json_decode($request->get('upload'), TRUE);
        $folder = $json['folder'];
        $dateNow = Carbon::now();
        $storeTo = $dateNow->addDay();

        if ($request->get('store_time') == 2) {
            $storeTo = $dateNow->addDays(7);
        }
        if ($request->get('store_time') == 3) {
            $storeTo = $dateNow->addDays(30);
        }

        $temporaryFile = TemporaryFile::where('folder', $folder)->first();

        UploadFile::create([
            'password' => $password,
            'link' => $folder,
            'link_expire' => $storeTo,
            'folder' => $temporaryFile->folder,
            'filename' => $temporaryFile->filename,
        ]);


        if($temporaryFile) {
            $tmpFilePath = storage_path('app/uploads/tmp/' . $temporaryFile->folder);
            $permFilePath = storage_path('app/uploads/perm/' . $temporaryFile->folder);
            $permDir = storage_path('app/uploads/perm/');
            if(!File::exists($permDir)) {
                File::makeDirectory($permDir);
            }
            File::move($tmpFilePath, $permFilePath);
        }

        $upload = new UploadFile();
        $fileByLink = $upload->getByLink($folder);

        $request->session()->put('password', sha1($fileByLink->password));
        $request->session()->put('link', $fileByLink->link);

        return Redirect::route('upload.show', [$fileByLink->link]);
    }

    public function find(Request $request, $link)
    {
        $file = UploadFile::where('link', $link)->firstOrFail();
        if(!$file) {
            abort(404);
        }
        if($request->session()->get('password') === sha1($file->password)){
            return Redirect::route('file.show', $link);
        }

        return Redirect::route('upload.password', ['link' => $file->link]);
    }

    public function auth(Request $request, $link)
    {
        $upload = UploadFile::where('link', $link)->firstOrFail();
        if(!$upload) {
            abort(404);
        }

        if($upload->password === $request->password) {
            $request->session()->put('password', sha1($request->password));
            $request->session()->put('link', $link);
            return Redirect::route('upload.show', $link);
        }

        return Redirect::route('upload.password', ['link' => $upload->link])->withErrors('Bad password');
    }

    public function show(Request $request, $link)
    {
        $sessionPassword = $request->session()->get('password');
        $sessionLink = $request->session()->get('link');
        $file = UploadFile::where('link', $link)->firstOrFail();
        if($link !== $sessionLink || $sessionPassword === null) {
            return Redirect::route('upload.password', ['link' => $link]);
        }

        if($sessionPassword === sha1($file->password)) {
            $url = route('upload.download', ['link' => $link]);
            return response()->view('show', ['file' => $file, 'url' => $url]);
        }

    }

    public function download(Request $request, $link)
    {
        $sessionPassword = $request->session()->get('password');
        $sessionLink = $request->session()->get('link');
        $file = UploadFile::where('link', $link)->firstOrFail();

        if($sessionPassword === sha1($file->password) && $link == $sessionLink) {
            $upload = 'uploads/perm/' . $file->folder . '/' . $file->filename;
            return Storage::download($upload);
        }

        return 'Error';
    }

    private function deleteExpired()
    {
        // TODO
    }

    private function deleteTemporaryFiles()
    {
        // TODO
    }
}
