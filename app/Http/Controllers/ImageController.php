<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image;
use App\User;
use App\Group;
use App\Session;
use Exception;
use Illuminate\Support\Facades\File;
use Firebase\JWT\JWT;
use Intervention\Image\Facades\Image as ImageService;
use Illuminate\Validation\Rule;

class ImageController extends Controller
{
    // adds image
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
            case 'session':
                \DB::table('session_image')->insert([
                    'session_id' => $request->input('parent_id'),
                    'image_id' => $image->id
                ]);
                break;
        }

        return response()->json([
            'message' => 'Image uploaded',
            'filename' => $filename
        ]);
    }

    // removes image
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
            case 'session':
                $element = \DB::table('session_image')->where('image_id', $request->input('image_id'))->first();
                break;
        }

        // remove
        // try {
            $image = Image::findOrFail($element->image_id);
            // TODO: remove files from file system
            File::delete('images/'.$image->filename);
            File::delete('images/thumb-'.$image->filename);
            Image::destroy($element->image_id);
            if ($request->input('type') == 'session') {
                \DB::delete('DELETE FROM session_image WHERE image_id='.$request->input('image_id'));
            } else {
                $element->update(['image_id' => null]);
            }
        // } catch(Exception $e) {
        //     return response()->json([
        //         'error' => "There was a problem with deleting the image"
        //     ], 400);
        // }

        return response()->json([
            'message' => 'Image deleted'
        ]);
    }

    protected function validateRequest(Request $request, $isUpload) {
        $this->validate($request, [
            'type' => [
                'required',
                Rule::in(['user', 'group', 'session'])
            ],
            'parent_id' => 'required'
        ]);

        if ($isUpload) {
            $this->validate($request, ['image' => 'required|file']);
        }

        $userId = $request->user->id;
        // check authorization
        switch ($request->input('type')) {
            case 'user':    // is site admin or it's his/her own photo
                if ($userId != $request->input('parent_id') && $request->user->is_admin != 1) {
                    return response()->json([
                        'error' => 'Not authorized'
                    ], 404);
                };
                break;
            case 'group':   // is site admin or group admin
                if ($request->user->is_admin != 1 && !\DB::table('group_user')->where('user_id', $userId)->where('group_id', $request->input('parent_id'))->where('is_group_admin', true)->exists()) {
                    return response()->json([
                        'error' => 'Not authorized'
                    ], 404);
                };
                break;
            case 'session': // is site admin or group admin
                if ($request->user->is_admin != 1 && !\DB::table('group_user')->where('user_id', $userId)->where('group_id', $request->input('parent_id'))->where('is_group_admin', true)->exists()) {
                    return response()->json([
                        'error' => 'Not authorized'
                    ], 404);
                };
                break;
        }

        return $request->user;
    }
}
