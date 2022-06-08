<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

class FeedController extends Controller {
    private $loggedUser;
    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function create(Request $r) {
        $returnArray = ['error' => ''];
        $allowedtypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $type = $r->input('type');
        $body = $r->input('body');
        $photo = $r->file('photo');

        if ($type) {
            switch ($type) {
                case 'text':
                    if (!$body) {
                        $returnArray['error'] = 'Something went wrong with text';
                    }
                    break;
                case 'photo':
                    if ($photo) {
                        if (in_array($photo->getClientMimeType(),  $allowedtypes)) {
                            $filename = md5(time() . rand(0, 9999)) . '.jpg';
                            $destPath = public_path('/media/uploads');
                            $img = Image::make($photo->path())
                                ->resize(800, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                })
                                ->save($destPath . '/' . $filename);

                            $body = $filename;
                        } else {
                            $returnArray['error'] = 'Image type not allowed';
                        }
                    } else {
                        $returnArray['error'] = 'Something went wrong with image';
                    }
                    break;
                default:
                    $returnArray['error'] = 'Unvalid post type';
                    break;
            }

            if ($body) {
                $newPost = new Post();
                $newPost->id_user = $this->loggedUser['id'];
                $newPost->type = $type;
                $newPost->created_at = date('Y-m-d H:i:s');
                $newPost->body = $body;
                $newPost->save();
            }
        } else {
            $returnArray['error'] = 'Data not found';
        }

        return $returnArray;
    }
}
