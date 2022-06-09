<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller {

    private $loggedUser;

    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function update(Request $r) {
        $returnArray = ['error' => ''];

        $name = $r->input('name');
        $email = $r->input('email');
        $birthdate = $r->input('birthdate');
        $city = $r->input('city');
        $work = $r->input('work');
        $password = $r->input('password');
        $password_confirm = $r->input('password_confirm');

        $user = User::find($this->loggedUser['id']);

        if ($name) {
            $user->name = $name;
        }

        if ($email) {
            if ($email != $user->email) {
                $emailExists = User::where('email', $email)->count();
                if ($emailExists === 0) {
                    $user->email = $email;
                } else {
                    $returnArray['error'] = 'E-mail already exists';
                }
            }
        }

        if ($birthdate) {
            if (strtotime($birthdate) === false) {
                $returnArray['error'] = 'Not a valid birthdate';
            }
            $user->birthdate = $birthdate;
        }

        if ($city) {
            $user->city = $city;
        }

        if ($work) {
            $user->work = $work;
        }

        if ($password && $password_confirm) {
            if ($password === $password_confirm) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $user->password = $hash;
            } else {
                $returnArray['error'] = 'Passwords do not match';
            }
        }

        $user->save();

        return $returnArray;
    }

    public function updateAvatar(Request $r) {
        $returnArray = ['error' => ''];

        $allowedtypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $r->file('avatar');

        if ($image) {
            if (in_array($image->getClientMimeType(), $allowedtypes)) {
                $user = User::find($this->loggedUser['id']);

                $filename = md5(time() . rand(0, 9999)) . 'jpg';

                $destPath = public_path('/media/avatars');

                $image = Image::make($image->path())
                    ->fit(200, 200)
                    ->save($destPath . '/' . $filename);
                $user->avatar = $filename;
                $user->save();

                $returnArray['url'] = url('/media/avatars/' . $filename);
            } else {
                $returnArray['error'] = 'Not a valid image format';
            }
        } else {
            $returnArray['error'] = 'Upload failed';
        }

        return $returnArray;
    }

    public function updateCover(Request $r) {
        $returnArray = ['error' => ''];

        $allowedtypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $r->file('cover');

        if ($image) {
            if (in_array($image->getClientMimeType(), $allowedtypes)) {
                $user = User::find($this->loggedUser['id']);

                $filename = md5(time() . rand(0, 9999)) . 'jpg';

                $destPath = public_path('/media/covers');

                $image = Image::make($image->path())
                    ->fit(850, 310)
                    ->save($destPath . '/' . $filename);
                $user->cover = $filename;
                $user->save();

                $returnArray['url'] = url('/media/covers/' . $filename);
            } else {
                $returnArray['error'] = 'Not a valid image format';
            }
        } else {
            $returnArray['error'] = 'Upload failed';
        }

        return $returnArray;
    }

    public function read($id = false) {
        $returnArray = ['error' => ''];

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

        $returnArray['data'] = $userInfo;

        return $returnArray;
    }

    public function follow($id) {
        $data = ['error' => ''];
        $userExists = User::find($id);

        if ($id == $this->loggedUser['id']) {
            $data = ['error' => 'You can not follow yourself'];
        }
        if (!$userExists) {
            $data = ['error' => 'You can not follow someone that does not exist'];
        } else {
            $isRelated = UserRelation::where('user_from', $this->loggedUser['id'])
                ->where('user_to', $id)
                ->first();
            if ($isRelated) {
                $isRelated->delete();
            } else {
                $newRelation = new UserRelation();
                $newRelation->user_from = $this->loggedUser['id'];
                $newRelation->user_to = $id;
                $newRelation->save();
            }
        }

        return $data;
    }
}
