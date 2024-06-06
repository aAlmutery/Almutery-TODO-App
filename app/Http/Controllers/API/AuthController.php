<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{

  /** Accept 
   * 
   * email, password
   * 
   */
  public function logIn(Request $request) 
  {
    $inputs = $request->all();

    $validation = Validator::make($inputs,[
      'email' => 'required|email',
      'password' => 'required|min:8'
    ]);

    if($validation->fails()){
      return response()->json([
        'status' => 'failed',
        'status_code' => 422,
        'message' => 'Validation Error',
        'Errors' => $validation->errors()
      ],422);
    }

    try {

      if( Auth::attempt(['email' => $inputs['email'] , 'password' => $inputs['password'] ]) ){

        $user = User::find(Auth::user()->id);

        // if we want to restrict user to login in one device only ⬇️
        $request->user()->tokens()->delete();

        return response()->json([
          'status' => 'success',
          'status_code' => 200,
          'message' => 'Authanticated User',
          'Token' => $user->createToken('auth-token')->plainTextToken,
          // 'userType'=> isset($user->user_type) && $user->user_type != null && $user->user_type != '' ? $user->user_type : 1,
          'userName'=> $user->name,
          'userEmail'=> $user->email,
          'id' => $user->id,
          ],200);

      }else {
        
        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => "email Or password does't match ",
          ],403);
      }

    } catch (\Throwable $th) {
      //throw $th;
      return response()->json([
        'status' => 'failed',
        'status_code' => 500,
        'message' => 'Somthing went Wrong with the server',
        // 'message'=> $th,
        ],500);
    }
  }

  /** Accept 
   * 
   * name, email, password, role_id
   * 
   */
  public function createUser(Request $request) 
  {

    // first check if the user is owner or not
    $checkOwner = RoleController::isOwner(Auth::user()->id);
    
    if($checkOwner){
      // If Owner
      $inputs = $request->all();

      $validation = Validator::make($inputs,[
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8',
        'role_id' => 'required'
      ]);

      if($validation->fails()){
        return response()->json([
          'status' => 'failed',
          'status_code' => 422,
          'message' => 'Validation Error',
          'Errors' => $validation->errors()
        ],422);
      }

      try {

        $checkUserExist = User::where('email' , $inputs['email'])->first();

        if( $checkUserExist ){

          return response()->json([
            'status' => 'failed',
            'status_code' => 403,
            'message' => "This Email is already exist",
            ],403);

        }else {
          // create User

          $data = array();
          $data['name'] = $inputs['name'];
          $data['password'] = Hash::make($inputs['password']);
          $data['email'] = $inputs['email'];

          if($inputs['role_id'] == 1){

            User::create($data)->assignRole('Owner')->syncPermissions([1,2,3,4,5,6,7,8,9,10,11,12,13]);

            return response()->json([
              'status' => 'success',
              'status_code' => 201,
              'message' => 'User Created Successfully',
              ],201);

          } else if ($inputs['role_id'] == 2){

            User::create($data)->assignRole('Developer')->syncPermissions([5,6,9,10]);

            return response()->json([
              'status' => 'success',
              'status_code' => 201,
              'message' => 'User Created Successfully',
              ],201);

          } else if ($inputs['role_id'] == 3){

            User::create($data)->assignRole('Tester')->syncPermissions([7,11]);

            return response()->json([
              'status' => 'success',
              'status_code' => 201,
              'message' => 'User Created Successfully',
              ],201);

          }else{
            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "There is no such role",
            ],403);
          }

        }

      } catch (\Throwable $th) {
        //throw $th;
        return response()->json([
          'status' => 'failed',
          'status_code' => 500,
          'message' => 'Somthing went Wrong with the server',
          // 'message' => $th,
          ],500);
      }

    } else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner To create New User",
        ],403);
    }
  }

  /** Accept 
   * 
   * id, role_id
   * 
   */
  public function changeUserRole(Request $request){

    // first check if the user is owner or not
    $checkOwner = RoleController::isOwner(Auth::user()->id);
    
    if($checkOwner){
      // If Owner

      $inputs = $request->all();
      // dd($inputs);

      $validation = Validator::make($inputs,[
        'id' => 'required',
        'role_id' => 'required'
      ]);
      
      if($validation->fails()){
        return response()->json([
          'status' => 'failed',
          'status_code' => 422,
          'message' => 'Validation Error',
          'Errors' => $validation->errors()
        ],422);
      }
      
      try {
        $userExist = User::with('roles')->find($inputs['id']);

        if($userExist){

          if($inputs['id'] != Auth::user()->id){

            if($inputs['role_id'] == 1 || $inputs['role_id'] == 2 || $inputs['role_id'] == 3){

              $roleName = ["Owner","Developer","tester"];

              // Delete Old Role
              $userExist->removeRole($userExist->roles[0]->name);

              // Add New Role
              $userExist->assignRole($roleName[$inputs['role_id']]);

              return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'User Role Updated Successfully',
              ],200);

            } else {

              return response()->json([
                'status' => 'failed',
                'status_code' => 403,
                'message' => "There is no such role",
              ],403);
            }

          }else {
            // I use this because the system need to have at least one (Product Owner)

            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "You can not change your role",
            ],403);

          }
        } else {
          return response()->json([
            'status' => 'failed',
            'status_code' => 403,
            'message' => "User does not exist",
          ],403);
        }

      } catch (\Throwable $th) {
        //throw $th;
        return response()->json([
          'status' => 'failed',
          'status_code' => 500,
          'message' => 'Somthing went Wrong with the server',
        ],500);
      }

    } else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner To Change User Role",
        ],403);
    }
  }

  public function logOut(Request $request)
  {
    try{

      $check = $request->user()->tokens()->delete();
      // dd(User::find(Auth::user()->id));
      // $check = User::find(Auth::user()->id)->tokens()->delete();
      if($check){

        return response()->json([
          'status' => 'success',
          'status_code' => 200,
          'message' => 'User Successfully Logged Out',
        ],200);
      }
      else{

        return response()->json([
          'status' => 'failed',
          'status_code' => 406,
          'message' => 'Could\'t delete Authenticated User',
        ],406);
      }


    } catch (\Throwable $th) {
      //throw $th;
      return response()->json([
        'status' => 'failed',
        'status_code' => 500,
        'message' => 'Somthing went Wrong with the server',
        'message2' => $th,
        ],500);

    }
  }
  
}
