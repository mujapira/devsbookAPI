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
        $data = ['owner' => ''];

        $txt = $r->input('txt');
        $postExists = Post::find($id);
        $userInfo = User::find($id);

        $data['owner']['avatar'] = url('media/avatars/' . $userInfo['avatar']);
        $data['owner']['cover'] = url('media/covers/' . $userInfo['cover']);
        $data['owner']['cover'] = $userInfo['name'];

        if ($postExists) {

            if ($txt) {
                $newComment = new PostComment();
                $newComment->id_post = $id;
                $newComment->id_user = $this->loggedUser['id'];
                $newComment->created_at = date('Y-m-d H:i:s');
                $newComment->body = $txt;
                $newComment->save();


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
/* 
 if ($id) {
            $userInfo = User::find($id);
            if (!$userInfo) {
                $returnArray['error'] = 'User not found';
            }
        } else {
            $userInfo = $this->loggedUser;
        }

        $userInfo['avatar'] = url('media/avatars/' . $userInfo['avatar']);
        $userInfo['cover'] = url('media/covers/' . $userInfo['cover']);

        $userInfo['me'] = ($userInfo['id'] == $this->loggedUser['id']) ? true : false;

        $dateFrom = new \DateTime($userInfo['birthdate']);
        $dateTo = new \DateTime('today');
        $userInfo['age'] = $dateFrom->diff($dateTo)->y;

        $userInfo['followers'] = UserRelation::where('user_to', $userInfo['id'])->count();
        $userInfo['following'] = UserRelation::where('user_from', $userInfo['id'])->count();
        $userInfo['photoCount'] = Post::where('id_user', $userInfo['id'])
            ->where('type', 'photo')
            ->count();


        $hasRelation = UserRelation::where('user_from', $this->loggedUser['id'])
            ->where('user_to', $userInfo['id'])->count();

        $userInfo['isFollowing'] = ($hasRelation > 0) ? true : false;

        $returnArray['data'] = $userInfo; */