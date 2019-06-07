<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image;
use App\User;
use App\Group;
use Exception;
use Illuminate\Support\Facades\File;
use Firebase\JWT\JWT;
use Intervention\Image\Facades\Image as ImageService;
use Illuminate\Validation\Rule;

class ImageController extends Controller
{
    function uploadImage(Request $request) {
        $credentials = $this->validateRequest($request, true);

        $filename = $request->input('type').'-'.$request->input('parent_id').'-'.mt_rand(1000000, 9999999).'.'.$request->image->extension();
        $request->file('image')->move('images', $filename);
        File::copy('images/' . $filename, 'images/thumb-' . $filename);

        // resize
        try {
            // resizing image
            $img = ImageService::make('images/'.$filename);
            $img->resize(2560, 2560, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save();

            // trimming to square
            $thumb = ImageService::make('images/thumb-'.$filename);
            $dim = min($thumb->height(), $thumb->width());
            $thumb->resizeCanvas($dim, $dim, 'center');
            // resize
            $thumb->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumb->save();
        } catch (NotReadableException $e) {
            $created->delete();
            return response()->json('There was a problem uploading your photo, was not readable.', 500);
        }

        // create Image in DB
        $image = Image::create([
            'filename' => $filename,
            'uploaded_by' => $credentials->id
        ]);

        // relationships in DB
        switch ($request->input('type')) {
            case 'user':
                User::findOrFail($request->input('parent_id'))->update(['image_id' => $image->id]);
                break;
            case 'group':
                Group::findOrFail($request->input('parent_id'))->update(['image_id' => $image->id]);
                break;
        }

        return response()->json([
            'message' => 'Image uploaded',
            'filename' => $filename
        ]);
    }

    function removeImage(Request $request) {
        $this->validateRequest($request, false);

        // find parent
        $element = NULL;
        switch ($request->input('type')) {
            case 'user':
                $element = User::findOrFail($request->input('parent_id'));
                break;
            case 'group':
                $element = Group::findOrFail($request->input('parent_id'));
                break;
        }

        // remove
        try {
            $image = Image::findOrFail($element->image_id);
            // File::delete('public/images/'.$image->$filename);
            // File::delete('public/images/thumb-'.$image->$filename);
            $element->update(['image_id' => null]);
            Image::destroy($element->image_id);
        } catch(Exception $e) {
            return response()->json([
                'error' => "There was a problem with deleting the image"
            ], 400);
        }

        return response()->json([
            'message' => 'Image deleted'
        ]);
    }

    protected function validateRequest(Request $request, $isUpload) {
        $this->validate($request, [
            'type' => [
                'required',
                Rule::in(['user', 'group'])
            ],
            'parent_id' => 'required'
        ]);

        if ($isUpload) {
            $this->validate($request, ['image' => 'required|file']);
        }

        $userId = $request->user->id;
        // check authorization
        if ($request->input('type') == 'user' && $userId != $request->input('parent_id') && $credentials->is_admin != 1) {
            return response()->json([
                'error' => 'Not authorized'
            ], 404);
        }
        // TODO for groups

        return $request->user;
    }
}
