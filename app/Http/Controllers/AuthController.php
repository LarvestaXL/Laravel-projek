<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index(){
        return view('auth.login');
    }
    public function login()
    {
    $credentials = request(['email', 'password']);
    /*     dd($credentials); */
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['Email or Password is wrong!' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'nama_member' => 'required',
            'no_hp' => 'required',
            'email' => 'required|email',
            'password' => 'required|same:konfirmasi_password',
            'konfirmasi_password' => 'required|same:password'
        ]);

        if ($validator->fails()){
            return response()->json(
                $validator->errors(),
                422
            );
        };

        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        unset($input['konfirmasi_password']);
        $member = Member::create($input);

        return response()->json([
            'data' => $member
        ]);
    }

    public function login_member(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        if ($validator->fails()){
            return response()->json(
                $validator->errors(),
                422
            );
        }
    
       $credentials = $request->only('email', 'password');
       $member = Member::where('email', $request->email)->first();
              
       if($member){

        if(Hash::check($request->password, $member->password)){
            $request->session()->regenerate();
            return response()->json([
                'mesage' => 'Success Login',
                'data' => $member
            ]);
        }else{
            return response()->json([
                'message' => 'failed',
                'data' => 'password is wrong'
            ]);    
        }
        
       }else{
        return response()->json([
            'message' => 'failed',
            'data' => 'Email is wrong'
        ]);
       }
    }

    public function logout(){
        auth()->logout();
        return response()->json(['message' => 'Successfully Logout']);
    }
    public function logout_member(){
        Session::flush();
        return response()->json(['message' => 'Successfully Logout Member']);
        redirect('/login');
    }


}



