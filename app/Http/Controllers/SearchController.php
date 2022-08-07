<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller {

    private $loggedUser;
    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function search(Request $r) {
        $data = ['error' => '', 'users' => []];
        $txt = $r->input('txt');

        if ($txt) {
            $userList = User::where('name', 'like', '%' . $txt . '%')->get();
            foreach ($userList as $singleUser) {
                $data['users'][] = [
                    'id' => $singleUser['id'],
                    'name' => $singleUser['name'],
                    'avatar' => url('media/avatars/' . $singleUser['avatar'])
                ];
            }
        } else {
            $data['error'] = 'No input information';
        }

        return $data;
    }
}
