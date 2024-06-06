<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RevisionTask;
use App\Models\StatusList;
use App\Models\TaskLogs;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Console\View\Components\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
  public function index()
  {
    try {

      $status = StatusList::all(['id', 'name']);

      $Tasks = Tasks::whereNull('parent')->get(['id', 'title', 'description', 'parent', 'status', 'assign_to', 'due_date']);

      foreach ($Tasks as $task) {

        if ($this->haveSubTask($task->id)) {
          $task->SubTask = $this->getSubTask($task->id);
          $task->description = substr($task->description, 0, 30) . '... ';
        } else {
            $task->SubTask = [];
            $task->description = substr($task->description, 0, 30) . '... ';
        }
      }

      $users = User::all();

      foreach ($users as $user) {
        $uRole = User::find($user->id);
        $user->role = ['id' => $uRole->roles[0]->id, 'name' => $uRole->roles[0]->name];
      }

      return response()->json([
        'status' => 'success',
        'status_code' => 200,
        'message' => 'Get Data',
        'tasks' => $Tasks,
        'users' => $users,
        'status_list' => $status,
      ], 200);

    } catch (\Throwable $th) {
      //throw $th;
      return $th;
    }
  }

  /** Accept 
   * 
   * (int) Parent_id => Task
   * 
   */
  private function haveSubTask($parent)
  {
    $subTask = Tasks::where('parent', $parent)->count();

    if ($subTask > 0)
      return true;

    return false;
  }

  /** 
   * Accept 
   * (int) Parent_id => Task,filter Optional,
   */
  private function getSubTask($parent, $filter = null)
  {
    $subTasks = array();
    
    if(isset($filter) && $filter != null){

      $Tasks = Tasks::where('parent', $parent);
      
      if (isset($filter['id']) && $filter['id'] != null)
      $Tasks = $Tasks->where('id', $filter['id']);
    
      if (isset($filter['title']) && $filter['title'] != null)
      $Tasks = $Tasks->where('title', 'like', '%' . $filter['title'] . '%');
      
      if (isset($filter['description']) && $filter['description'] != null)
      $Tasks = $Tasks->where('description', 'like', '%' . $filter['description'] . '%');

      if (isset($filter['assign_to']) && $filter['assign_to'] != null)
      $Tasks = $Tasks->where('assign_to', $filter['assign_to']);

      $Tasks = $Tasks->get(['id', 'title', 'description', 'parent', 'status', 'assign_to', 'due_date']);

    } else{

      $Tasks = Tasks::where('parent', $parent)->get(['id', 'title', 'description', 'parent', 'status', 'assign_to', 'due_date']);
    }

    foreach ($Tasks as $task) {
      if ($this->haveSubTask($task->id)) {

        $task->description = substr($task->description, 0, 30) . '... ';
        $task->SubTask = $this->getSubTask($task->id);
      } else {

        $task->SubTask = [];
        $task->description = substr($task->description, 0, 30) . '... ';
      }
      $subTasks[] = $task;
    }
    return $subTasks;
  }

  public function create()
  {
    // first check if the user is owner or not
    $checkOwner = RoleController::isOwner(Auth::user()->id);

    if($checkOwner){
      // If Owner
      $status = StatusList::all(['id', 'name']);

      // only get developer users to assign 
      $users = User::role('Developer')->get();

      return response()->json([
        'status' => 'success',
        'status_code' => 200,
        'message' => 'Get Needed Combo Data',
        'users' => $users,
        'status_list' => $status,
      ], 200);

    }else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner Accses",
      ],403);
    }
  }

  /**
   * Accept
   * title, description, (int) status, (int) assign_to, due_date, (int) parent
   */
  public function store(Request $request)
  {
    // first check if the user is owner or not
    $checkOwner = RoleController::isOwner(Auth::user()->id);

    if($checkOwner){
      // If Owner
      $proceed = true;
      $inputs = $request->all();

      $validation = Validator::make($inputs,[
        'title' => 'required'
      ]);

      if($validation->fails()){
        return response()->json([
          'status' => 'failed',
          'status_code' => 422,
          'message' => 'Validation Error',
          'Errors' => $validation->errors()
        ],422);
      }


      if(isset($inputs['parent']) && $inputs['parent'] != null){
        $taskCheck = Tasks::find($inputs['parent']);

        if(!$taskCheck){
          $proceed = false;
          $FailsAt = 'parent';
        }
      }else if(!isset($inputs['parent'])){
        $inputs['parent'] = null;
      }

      if(isset($inputs['assign_to']) && $inputs['assign_to'] != null){
        $userCheck = User::find($inputs['assign_to']);

        if(!$userCheck){
          $proceed = false;
          $FailsAt = 'assign_to';
        }
      }else if(!isset($inputs['assign_to'])){
        $inputs['assign_to'] = null;
      }

      if(isset($inputs['status']) && $inputs['status'] != null){
        $statusCheck = User::find($inputs['status']);

        if(!$statusCheck){
          $proceed = false;
          $FailsAt = 'status';
        }

      } else if(!isset($inputs['status'])){
        $inputs['status'] = 1;
      }

      if(!isset($inputs['assign_to']) ){
        $inputs['assign_to'] = null;
      }

      if(!isset($inputs['description']) ){
        $inputs['description'] = null;
      }

      if(!isset($inputs['due_date']) ){
        $inputs['due_date'] = null;
      }

      if($proceed){
        // save
        try {

          $task = Tasks::create([
            'title' => $inputs['title'],
            'description' => $inputs['description'] ,
            'created_at' => now(),
            'created_by' => Auth::user()->id,
            'status' => $inputs['status'],
            'parent' => $inputs['parent'],
            'assign_to' => $inputs['assign_to'],
            'due_date' => $inputs['due_date'] ,
          ]);

          $this->createLog($task->id, 'Create Task');
  
          return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'message' => 'Task Created Successfully',
          ],201);
  
        } catch (\Throwable $th) {
          //throw $th;
          return response()->json([
            'status' => 'failed',
            'status_code' => 500,
            'message' => 'Somthing went Wrong with the server',
          ],500);
        }
        
      }else {
        // fails 
        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => 'The ' . $FailsAt . ' you provided does not mach with records we have' ,
        ],403);
      }

    }else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner to Store Task info ",
      ],403);
    }
  }

  public function createLog($taskID, $name){

    $user = User::find(Auth::user()->id);

    TaskLogs::create([
      'role_name' => $user->roles[0]->name,
      'name' => $name,
      'task_id' => $taskID,
      'created_by' => $user->id,
      'user_name' => $user->name,
    ]);

  }

  public function show(Request $request, $taskID)
  {
    if($taskID){
      $taskCheck = Tasks::find($taskID);

      if($taskCheck){
        // task is in the DB

        $getTask = Tasks::with('createdBy')->where('id', $taskID)->first();
        return response()->json([
          'status' => 'success',
          'status_code' => 200,
          'message' => 'Get Data',
          'task' => $getTask,
        ], 200);

      }else{

        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => "Task does not exist",
        ],403);

      }
    }else{
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => 'This task does not exist' ,
      ],403);
    }
  }

  public function edit(Request $request, $taskID)
  {
    $checkOwner = RoleController::isOwner(Auth::user()->id);

    if($checkOwner){
      // If Owner
      
      $status = StatusList::all(['id', 'name']);

      // only get developer users to assign 
      $users = User::role('Developer')->get();

      $task = Tasks::with(['createdBy', 'status', 'assignTo'])->where('id', $taskID)->first();


      return response()->json([
        'status' => 'success',
        'status_code' => 200,
        'message' => 'Get Needed Combo Data',
        'users' => $users,
        'status_list' => $status,
        'task' => $task,
      ], 200);

    }else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner Accses",
      ],403);
    }
  }

  /**
   * Accept
   * title, description, (int) status, (int) assign_to, due_date, (int) parent
   */
  public function update(Request $request, $taskID)
  {
    // first check if the user is owner or not
    $checkOwner = RoleController::isOwner(Auth::user()->id);

    if($checkOwner){
      // If Owner
      $proceed = true;
      $inputs = $request->all();

      $validation = Validator::make($inputs,[
        'title' => 'required',
        'status' => 'required',
      ]);

      if($validation->fails()){
        return response()->json([
          'status' => 'failed',
          'status_code' => 422,
          'message' => 'Validation Error',
          'Errors' => $validation->errors()
        ],422);
      }

      if(isset($inputs['parent']) && $inputs['parent'] != null){
        $taskCheck = Tasks::find($inputs['parent']);

        if(!$taskCheck){
          $proceed = false;
          $FailsAt = 'parent';
        }
      }else if(!isset($inputs['parent'])){
        $inputs['parent'] = null;
      }

      if(isset($inputs['assign_to']) && $inputs['assign_to'] != null){
        $userCheck = User::find($inputs['assign_to']);

        if(!$userCheck){
          $proceed = false;
          $FailsAt = 'assign_to';
        }
      }else if(!isset($inputs['assign_to'])) {
        $inputs['assign_to'] = null;
      }

      if(!isset($inputs['description']) ){
        $inputs['description'] = null;
      }
      
      if(!isset($inputs['due_date']) ){
        $inputs['due_date'] = null;
      }

      if($proceed){
        // save
        try {

          $taskCheck = Tasks::find($taskID);

          if($taskCheck){
            // task is in the DB
            $statusCheck = StatusList::find($inputs['status']);

            if($statusCheck){

              $oldTask = Tasks::find($taskID);

              Tasks::where('id', $taskID)->update([
                'title' => $inputs['title'],
                'description' => $inputs['description'] ,
                'updated_at' => now(),
                'updated_by' => Auth::user()->id,
                'status' => $inputs['status'],
                'parent' => $inputs['parent'],
                'assign_to' => $inputs['assign_to'],
                'due_date' => $inputs['due_date'] ,
              ]);

              $newTask = Tasks::find($taskID);

              $this->createLog($taskID, 'Update Task');
              $this->createRevision($taskID, $newTask, $oldTask);

              return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Task Updated Successfully',
              ],201);

            }else {

              return response()->json([
                'status' => 'failed',
                'status_code' => 403,
                'message' => "status does not exist",
              ],403);
            }
            
          }else{
            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Task does not exist",
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

      }else {
        // fails 
        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => 'The ' . $FailsAt . ' you provided does not mach with records we have' ,
        ],403);
      }

    }else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner To Update Task ",
      ],403);
    }
  }

  public function createRevision($taskID, $new, $was){

    $user = User::find(Auth::user()->id);

    $newJson = json_encode($new);
    $wasJson = json_encode($was);

    RevisionTask::create([
      'role_name' => $user->roles[0]->name,
      'was' => $wasJson,
      'new' => $newJson,
      'task_id' => $taskID,
      'created_by' => $user->id,
      'user_name' => $user->name,
    ]);

  }

  public function getRevision($taskID){
    $checkOwner = RoleController::isOwner(Auth::user()->id);

    if($checkOwner){
      // Owner
      $revisions = RevisionTask::where('task_id', $taskID)->orderByDesc('created_at')->get();

      foreach ($revisions as $revision) {
        $revision->was = json_decode($revision->was);
        $revision->new = json_decode($revision->new);
      }

      return response()->json([
        'status' => 'success',
        'status_code' => 200,
        'message' => 'Get Needed Combo Data',
        'revisions' => $revisions,
      ], 200);

    }else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner To Update Task ",
      ],403);
    }
  }

  public function destroy($taskID)
  {
    // first check if the user is owner or not
    $checkOwner = RoleController::isOwner(Auth::user()->id);

    if($checkOwner){
      // If Owner

      $taskCheck = Tasks::find($taskID);

      if($taskCheck){
        // task is in the DB

        try {
          $isDeleted = Tasks::where('id', $taskID)->delete();

          if($isDeleted == 1){
  
            return response()->json([
              'status' => 'success',
              'status_code' => 204,
              'message' => 'Task Deleted Successfully',
            ],200);
  
          } else{

            return response()->json([
              'status' => 'success',
              'status_code' => 409,
              'message' => 'Task can not be Deleted',
            ],200);
          }
  
        } catch (\Throwable $th) {
          //throw $th;
          return response()->json([
            'status' => 'failed',
            'status_code' => 500,
            'message' => 'Somthing went Wrong with the server',
          ],500);
        }
      }else{
        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => "Task does not exist",
        ],403);
      }

    }else {
      // Not Owner
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Permission Denied, You need to be Owner Delete Task",
      ],403);
    }
  }

  /** 
   * Accept 
   * (int) task_id, (int) assign_to (user)
   */
  public function changeAssignTo(Request $request){
    $inputs = $request->all();

    $validation = Validator::make($inputs,[
      'task_id' => 'required',
      'assign_to' => 'required',
    ]);

    if($validation->fails()){
      return response()->json([
        'status' => 'failed',
        'status_code' => 422,
        'message' => 'Validation Error',
        'Errors' => $validation->errors()
      ],422);
    }

    $taskID = $inputs['task_id'];

    $task = Tasks::find($taskID);

    if($task){

      $assignedUser = User::find($inputs['assign_to']);

      if ($assignedUser){

        // 1 => Owner || 2 => Developer || 3 => Tester
        $userRole = $assignedUser->roles[0]->id;
        $taskStatus = $task->status;

        // dd($userRole);
        if($userRole == 1){
          // Owner
          if($taskStatus == 4 /* PO Review */){
            // proceed
            
            Tasks::where('id', $taskID)->update([
              'assign_to' => $inputs['assign_to'],
              'updated_at' => now(),
              'updated_by' => Auth::user()->id
            ]);

            $newTask = Tasks::find($taskID);

            $this->createLog($taskID, 'Update Task');
            $this->createRevision($taskID, $newTask, $task);

            return response()->json([
              'status' => 'success',
              'status_code' => 201,
              'message' => 'Task hase been Assignd Successfully',
            ],201);

          }else{
            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Permission Denied, You Can not assign this Task to Owner, it should be assignd to Developer Or Tester ",
            ],403);
          }

        }else if($userRole == 2){
          // Developer

          if($taskStatus == 1 /* TO DO */ || $taskStatus == 2 /* IN PROGRESS */){
            // proceed

            Tasks::where('id', $taskID)->update([
              'assign_to' => $inputs['assign_to'],
              'updated_at' => now(),
              'updated_by' => Auth::user()->id
            ]);

            $newTask = Tasks::find($taskID);

            $this->createLog($taskID, 'Update Task');
            $this->createRevision($taskID, $newTask, $task);

            return response()->json([
              'status' => 'success',
              'status_code' => 201,
              'message' => 'Task hase been Assignd Successfully',
            ],201);

          }else{
            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Permission Denied, You Can not assign this Task to Developer, it should be assignd to Owner Or Tester ",
            ],403);
          }

        }else if($userRole == 3){
          // Tester

          if($taskStatus == 3 /* READY FOR TEST */){
            // proceed
            
            Tasks::where('id', $taskID)->update([
              'assign_to' => $inputs['assign_to'],
              'updated_at' => now(),
              'updated_by' => Auth::user()->id
            ]);

            $newTask = Tasks::find($taskID);

            $this->createLog($taskID, 'Update Task');
            $this->createRevision($taskID, $newTask, $task);

            return response()->json([
              'status' => 'success',
              'status_code' => 201,
              'message' => 'Task hase been Assignd Successfully',
            ],201);

          }else{
            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Permission Denied, You Can not assign this Task to Tester, it should be assignd to Owner Or Developer ",
            ],403);
          }
        }
        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => "NONE",
        ],403);
      } else {
        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => "This assign_to (user) does not exist",
        ],403);
      }

    } else {
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Task does not exist",
      ],403);
    }

  }

  /** 
   * Accept 
   * (int) task_id, (int) status
   */
  public function changeStatus(Request $request){

    $inputs = $request->all();

    $validation = Validator::make($inputs,[
      'task_id' => 'required',
      'status' => 'required',
    ]);

    if($validation->fails()){
      return response()->json([
        'status' => 'failed',
        'status_code' => 422,
        'message' => 'Validation Error',
        'Errors' => $validation->errors()
      ],422);
    }

    $taskID = $inputs['task_id'];

    $task = Tasks::find($taskID);
    
    if($task){
      $status = Tasks::find($inputs['status']);
      if($status){

        $oldStatus = $task->status;
        $newStatus = $inputs['status'];


        if(RoleController::isOwner(Auth::user()->id)){
          // Owner
          $ownerHaveActionOn = [1, 2, 3, 4, 5];
          if(in_array($oldStatus, $ownerHaveActionOn)){
            // Have Action
            $ownerCanMoveTo = [2, 5, 6];
            if(in_array($newStatus, $ownerCanMoveTo)){

              Tasks::where('id', $taskID)->update([
                'status' => $newStatus,
                'updated_at' => now(),
                'updated_by' => Auth::user()->id
              ]);

              $newTask = Tasks::find($taskID);

              $this->createLog($taskID, 'Update Task');
              $this->createRevision($taskID, $newTask, $task);

              if($newStatus == 5 || $newStatus == 2 || $newStatus == 6){
                // autoAssign Developer
                $this->autoAssignDeveloperToTask($taskID);
              }

              return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Status hase been Changed Successfully',
              ],201);

            } else{

              return response()->json([
                'status' => 'failed',
                'status_code' => 403,
                'message' => "Permission Denied, You Can not move to this Status ",
              ],403);
            }

          } else{

            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Permission Denied, You need do not have Action on this status ",
            ],403);
          }

        } else if(RoleController::isDeveloper(Auth::user()->id)){
          // Developer
          $developerHaveActionOn = [1, 2];
          if(in_array($oldStatus, $developerHaveActionOn)){
            // Have Action

            $developerCanMoveTo = [2, 3];
            if(in_array($newStatus, $developerCanMoveTo)){

              Tasks::where('id', $taskID)->update([
                'status' => $newStatus,
                'updated_at' => now(),
                'updated_by' => Auth::user()->id
              ]);

              $newTask = Tasks::find($taskID);

              $this->createLog($taskID, 'Update Task');
              $this->createRevision($taskID, $newTask, $task);

              if($newStatus == 3){
                $this->autoAssignTesterToTask($taskID);
              }

              return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Status hase been Changed Successfully',
              ],201);

            } else{

              return response()->json([
                'status' => 'failed',
                'status_code' => 403,
                'message' => "Permission Denied, You Can not move to this Status ",
              ],403);
            }

          } else{

            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Permission Denied, You need do not have Action on this status ",
            ],403);
          }

        } else if(RoleController::isTester(Auth::user()->id)){
          // Tester

          if($task->assign_to == Auth::user()->id){

            $testerHaveActionOn = [3];
            if(in_array($oldStatus, $testerHaveActionOn)){
              // Have Action

              $testerCanMoveTo = [4];
              if(in_array($newStatus, $testerCanMoveTo)){

                Tasks::where('id', $taskID)->update([
                  'status' => $newStatus,
                  'updated_at' => now(),
                  'updated_by' => Auth::user()->id
                ]);
  
                $newTask = Tasks::find($taskID);
  
                $this->createLog($taskID, 'Update Task');
                $this->createRevision($taskID, $newTask, $task);
                
                if($newStatus == 4){
                  $this->autoAssignOwnerToTask($taskID);
                }
  
                return response()->json([
                  'status' => 'success',
                  'status_code' => 201,
                  'message' => 'Status hase been Changed Successfully',
                ],201);

              } else{

                return response()->json([
                  'status' => 'failed',
                  'status_code' => 403,
                  'message' => "Permission Denied, You Can not move to this Status ",
                ],403);
              }

            } else{

              return response()->json([
                'status' => 'failed',
                'status_code' => 403,
                'message' => "Permission Denied, You need do not have Action on this status ",
              ],403);
            }

          }else {
            return response()->json([
              'status' => 'failed',
              'status_code' => 403,
              'message' => "Permission Denied, This Task is not belong to you",
            ],403);
          }
          
        }

      } else{

        return response()->json([
          'status' => 'failed',
          'status_code' => 403,
          'message' => "Status does not exist",
        ],403);
      }

    } else {
      return response()->json([
        'status' => 'failed',
        'status_code' => 403,
        'message' => "Task does not exist",
      ],403);
    }
  }

  public function autoAssignDeveloperToTask($taskID){
    $logs = TaskLogs::where('task_id', $taskID)->orderByDesc('created_at')->get();

    $developerID = null;
    foreach ($logs as $log) {
      if($log->role_name == 'Developer'){
        $developerID = $log->created_by;
        break;
      }
    }

    if($developerID != null){
      Tasks::where('id', $taskID)->update([
        'assign_to' => $developerID
      ]);
      $this->createLog($taskID, 'Update Task');
    }
  }

  public function autoAssignOwnerToTask($taskID){
    $log = TaskLogs::where('task_id', $taskID)->where('name', 'Update Task')->orderByDesc('created_at')->first();

    if($log->created_by != null){
      Tasks::where('id', $taskID)->update([
        'assign_to' => $log->created_by
      ]);

      $this->createLog($taskID, 'Update Task');
    }
  }

  public function autoAssignTesterToTask($taskID){
    // $testers = User::with('Tester')->get();
    $testers = User::with('roles')->get()->filter(
      fn ($user) => $user->roles->where('name', 'Tester')->toArray()
    )->all();

    $result = array();

    foreach ($testers as $tester) {
      $noOfTasks = Tasks::where('assign_to', $tester->id)->count();
      $result[] =  ['id' => $tester->id, 'count' => $noOfTasks];
    }

    for ($i=0; $i < sizeof($result); $i++) { 
      if($i == 0)
        continue;

      if($result[$i -1]['count'] < $result[$i]['count']){
        // take id
        $assign = $result[$i -1]['id'];
      }else{
        $assign = $result[$i]['id'];

      }
    }

    Tasks::where('id', $taskID)->update([
      'assign_to' => $assign
    ]);

    $this->createLog($taskID, 'Update Task');
    
  }

  /** 
   * Accept 
   * (int) id, title, description, (int) assigned_to,
   */
  public function filteredTasks(Request $request)
  {

    $filter = $request->all();

    $Tasks = Tasks::whereNull('parent');

    if (isset($filter['id']) && $filter['id'] != null)
      $Tasks = $Tasks->where('id', $filter['id']);

    if (isset($filter['title']) && $filter['title'] != null)
      $Tasks = $Tasks->where('title', 'like', '%' . $filter['title'] . '%');

    if (isset($filter['description']) && $filter['description'] != null)
      $Tasks = $Tasks->where('description', 'like', '%' . $filter['description'] . '%');

    if (isset($filter['assign_to']) && $filter['assign_to'] != null)
      $Tasks = $Tasks->where('assign_to', $filter['assign_to']);

    $Tasks = $Tasks->get(['id', 'title', 'description', 'parent', 'status', 'assign_to', 'due_date']);

    foreach ($Tasks as $task) {

      if ($this->haveSubTask($task->id)) {
        $task->SubTask = $this->getSubTask($task->id, $filter);
      } else {
        $task->SubTask = [];
      }
    }


    return response()->json([
      'status' => 'success',
      'status_code' => 200,
      'message' => 'Get Filterd Data',
      'tasks' => $Tasks,
    ], 200);
  }
}
