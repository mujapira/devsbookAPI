<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
