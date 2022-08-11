<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private $loggedUser;
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function like($id)
    {
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

    public function comment(Request $r, $id)
    {
        $data = ['error' => ''];
        $txt = $r->input('txt');
        $postExists = Post::find($id);
        if ($postExists) {

            if ($txt) {
                $newComment = new PostComment();
                $newComment->id_post = $id;
                $newComment->id_user = $this->loggedUser['id'];
                $newComment->created_at = date('Y-m-d H:i:s');
                $newComment->body = $txt;
                $newComment->save();
                
                $userInfo = User::find($id);
                $data['owner'] = $userInfo;
                $data['id_post'] = $id;
                $data['id_user'] = $this->loggedUser['id'];
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['body'] =  $txt;
            } else {
                $data['error'] = 'Text not found';
            }
        } else {
            $data['error'] = 'Post not found';
        }
        return $data;
    }
}
