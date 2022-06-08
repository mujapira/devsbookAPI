<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller {

    public function __construct() {
        $this->middleware(
            'auth:api',
            ['except' =>
            [
                'login', 'create', 'unauthorized'
            ]]
        );
    }

    //Route::post('/user', [AuthController::class, 'create']);
    public function create(Request $r) {
        $returnArray = ['error' => '']; // Error array
        $name = $r->input('name'); //fields
        $email = $r->input('email');
        $password = $r->input('password');
        $birthdate = $r->input('birthdate');

        if ($name && $email && $password && $birthdate) {
            $emailExists = User::where('email', $email)->count();

            //birthdate validation
            if (strtotime($birthdate) === false) {
                $returnArray['error'] = 'Not a valid birthdate';
            }
            //email validation
            if ($emailExists === 0) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $newUser = new User();
                $newUser->name = $name;
                $newUser->email = $email;
                $newUser->password = $hash;
                $newUser->birthdate = $birthdate;
                $newUser->save();

                $token = Auth::attempt([
                    'email' => $email,
                    'password' => $password,

                ]);

                if (!$token) {
                    $returnArray['error'] = 'Error logging in';
                };
                $returnArray['token'] = $token;
            } else {
                $returnArray['error'] = 'This e-mail address is already in use';
            }
        } else {
            $returnArray['error'] = 'Missing required fields';
        }

        return $returnArray;
    }
}
