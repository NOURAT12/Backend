<?php

namespace App\Http\Controllers;

use App\Models\GroupUser;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\File as FileFile;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function badResponse($validate)
    {
        if (env('APP_DEBUG')) {
            return response()->json([
                'message' => 'invaled data',
                'errors' => $validate->errors(),
            ], 400);
        }
        return  response()->json([
            'message' => 'invaled data',
            'data' => null
        ], 400);
    }
    public function loginResponse($data, $token, $message = null)
    {
        return  response()->json([
            'message' => $message ? $message : 'login successfully',
            'data' => $data,
            'token' => $token
        ], 200);
    }
    public function failResponse($message)
    {
        return  response()->json([
            'message' => $message,
            'data' => null
        ], 400);
    }
    public function successResponse($message)
    {
        return  response()->json([
            'message' => $message,
        ], 200);
    }
    public function createResponse($data)
    {
        return  response()->json([
            'message' => 'created successfully',
            'data' => $data,
        ], 200);
    }
    public function updateResponse($data)
    {
        return  response()->json([
            'message' => 'updated successfully',
            'data' => $data,
        ], 200);
    }
    public function deleteResponse($data)
    {
        return  response()->json([
            'message' => 'deleted successfully',
            'data' => $data,
        ], 200);
    }
    public function getResponse($data)
    {
        return  response()->json([
            'message' => 'fetched successfully',
            'data' => $data,
        ], 200);
    }
    public function serverResponse()
    {
        return  response()->json([
            'message' => 'حدث خطأ ما يرجى المحاولة لاحقا',
            'data' => null,
        ], 500);
    }
    public function t401Response($message)
    {
        return  response()->json([
            'message' => $message,
            'data' => null,
        ], 401);
    }
    public function t403Response($message)
    {
        return  response()->json([
            'message' => $message,
            'data' => null,
        ], 403);
    }


    public function generatePassword()
    {
        $lowercase = range('a', 'z');
        $uppercase = range('A', 'Z');
        $digits = range(0, 9);
        $special = ['#', '$'];
        $chars = array_merge($lowercase, $uppercase, $digits, $special);
        $length = 8;

        do {
            $password = array();

            for ($i = 0; $i < $length; $i++) {
                $int = rand(0, count($chars) - 1);
                array_push($password, $chars[$int]);
            }
        } while (empty(array_intersect($special, $password)));

        $passwords = implode('', $password);
        return $passwords;
    }


    public function getUsername($username)
    {

        $i = 0;
        while (User::where('username', $username)->exists()) {
            $i++;
            $username = $username . $i;
        }

        return $username;
    }

    public function getSizeFile(FileFile $file)
    {
        $size = number_format($file->getSize() / 1000, 1);
        if ($size > 1000) {
            $arr = explode(',', $size);
            $size = $arr[0] . $arr[1];
            $size = number_format(floatval($size) / 1000, 1) . ' mb';
        } else {
            $size = $size . ' kb';
        }
        return $size;
    }

 
}
