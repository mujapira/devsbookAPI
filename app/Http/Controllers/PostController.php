<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller {
    private $loggedUser;
    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function like($id) {
        $returnArray = ['error' => ''];

        $postExists = Post::find($id);
        if ($postExists) {
            $isLiked = PostLike::where('id_post', $id)
                ->where('id_user', $this->loggedUser['id'])
                ->count();

            if ($isLiked > 0) {
                $pl = PostLike::where('id_post', $id)
                    ->where('id_user', $this->loggedUser['id'])
                    ->first();
                $pl->delete();
                $returnArray['isLiked'] = false;
            } else {
                $newPostLike = new PostLike();
                $newPostLike->id_post = $id;
                $newPostLike->id_user = $this->loggedUser['id'];
                $newPostLike->created_at = date('Y-m-d H:i:s');
                $newPostLike->save();

                $returnArray['isLiked'] = true;
            }

            $likeCount = PostLike::where('id_post', $id)->count();

            $returnArray['likeCount'] = $likeCount;
        } else {
            $returnArray['error'] = 'Post does not exist';
        }

        return $returnArray;
    }
}
