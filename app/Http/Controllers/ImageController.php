<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image;
use App\User;
use App\Group;
use Illuminate\Support\Facades\File;
use Firebase\JWT\JWT;
use Intervention\Image\Facades\Image as ImageService;

class ImageController extends Controller
{
    function uploadImage(Request $request) {

        // check request
        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return response()->json([
                'error' => 'Request image not provided'
            ], 400);
        }
        if (!$request->has('type') || !$request->has('parent_id')) {
            return response()->json([
                'error' => "Request missing 'type' or 'parent_id'"
            ], 400);
        }

        $token = $request->header('Authorization');
        $token = substr($token, 7-strlen($token));
        $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        $userId = $credentials->sub;

        // check authorization
        if ($request->input('type') == 'image' && $userId != $request->input('parent_id') && $credentials->is_admin != 1) {
            return response()->json([
                'error' => 'Not authorized'
            ], 404);
        }

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
            'uploaded_by' => $credentials->sub
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
}
