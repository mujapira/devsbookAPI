<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Comment;
use App\Models\PostComment;
use App\Models\UserRelation;
use App\Models\User;

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

    public function read(Request $r) {
        $returnArray = ['error' => ''];
        $page = intval($r->input('page'));
        $perPage = 2;
        $allUsers = [];

        $usersFollowedByLoggeduser = UserRelation::where('user_from', $this->loggedUser['id']);
        foreach ($usersFollowedByLoggeduser as $userFollowedByLoggeduser) {
            $allUsers[] = $userFollowedByLoggeduser['user_to'];
        }
        $allUsers[] = $this->loggedUser['id'];

        $PostListOrderedByCreatedAt = Post::whereIn('id_user', $allUsers)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $totalPostQuantity = Post::whereIn('id_user', $allUsers)->count();
        $pageCount = ceil(($totalPostQuantity / $perPage));
        $postList = $PostListOrderedByCreatedAt;

        $postsWithAdditionalInfo = $this->_postListToObjetc($postList, $this->loggedUser['id']);

        $returnArray['posts'] = $postsWithAdditionalInfo;
        $returnArray['pageCount'] = $pageCount;
        $returnArray['CurrentPage'] = $page;

        return $returnArray;
    }

    private function _postListToObjetc($postList, $loggedUserId) {
        foreach ($postList as $postKey => $postItem) {

            if ($postItem['id_user'] == $loggedUserId) {
                $postList[$postKey]['mine'] = true;
            } else {
                $postList[$postKey]['mine'] = false;
            }

            $userInfo = User::find($postItem['id_user']);
            $userInfo['avatar'] = url('media/avatars/' . $userInfo['avatar']);
            $userInfo['cover'] = url('media/covers/' . $userInfo['cover']);
            $postList[$postKey]['user'] = $userInfo;

            $likes = PostLike::where('id_post', $postItem['id'])->count();
            $postLikes[$postKey]['likeCount'] = $likes;

            $isSelfLiked = PostLike::where('id_post', $postItem['id'])
                ->where('id_user', $loggedUserId)
                ->count();
            $postList[$postKey]['selfLiked'] = ($isSelfLiked > 0) ? true : false;

            $comments = PostComment::where('id_post', $postItem['id'])->get();
            foreach ($comments as $commentKey => $comment) {
                $user = User::find($comment['id_user']);
                $comments[$commentKey]['user'] = $user;
                $user['avatar'] = url('media/avatars/' . $user['avatar']);
                $user['cover'] = url('media/covers/' . $user['cover']);
            }
            $postList[$postKey]['comments'] = $comments;
        }

        return $postList;
    }
}
