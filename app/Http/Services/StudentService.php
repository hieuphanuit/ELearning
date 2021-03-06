<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Entities\User;
use App\Entities\ParentStudent;
use App\Helpers\Statics\UserRolesStatic;
use App\Helpers\Statics\UserStatusStatic;
use App\Helpers\Statics\ParentStudentStatusStatic;
use App\Helpers\Traits\UploadImageTrait;

class StudentService {
    use UploadImageTrait;

    public function index($request)
    {
        $limit = $request->get('limit', 10);
        $keyword = $request->get('keyword', null);
        $status = $request->get('status', null);
        $studentsQuery = User::select(['id', 'name', 'email', 'description'])
            ->where('role', UserRolesStatic::STUDENT);
        
        if ($keyword) {
            $studentsQuery->where(function($query) use ($keyword){
                $query->where('email', 'like', '%'.$keyword.'%');
                $query->orWhere('name', 'like' , '%'.$keyword.'%');
            });
        }
        
        if (!is_null($status)) {
            $studentsQuery->where('status', $status);
        }

        $students = $studentsQuery->paginate($limit)
            ->appends(
                request()->query()
            );

        return response()
            ->json($students);         
    }

    public function register($request)
    {
        $data = $request->all();
        $avatar = $request->file('avatar');
        $filePath = $this->uploadAvatar($avatar);

        $userData = [
            'name' => $data['name'], 
            'email' => $data['email'], 
            'password' => Hash::make($data['password']), 
            'date_of_birth' => $data['date_of_birth'],
            'role' => UserRolesStatic::STUDENT,
            'description' => $data['description'],
            'avatar' => $filePath,
            'status' => UserStatusStatic::ACTIVE
        ];
        
        $user = User::create($userData);

        $studentData = [
            'phone_number' => $data['phone_number'],
            'school' => $data['school'],
            'class' => $data['class'],
        ];
        $user->studentInformation()->create($studentData);


        $user->load('studentInformation');

        $token = auth()->attempt([
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        return response()->json([
            'token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function info()
    {
        $user = \Auth::user();
        $user->load('studentInformation');

        return response()
            ->json($user);
    }

    public function detail($id)
    {
        $user = User::find($id);
        $user->load('studentInformation');
       
        return response()
            ->json($user);
    }

    public function subscribeStudent($id)
    {
        $user = Auth::user();
        $studentSubscribed = $user->childsSubscribed()->create(
            [
                'student_id' => $id,
                'status' => ParentStudentStatusStatic::PENDING
            ]
        );
        return response()
            ->json($studentSubscribed);
    }

    public function subscribedParentList($request)
    {
        $status = $request->get('status', ParentStudentStatusStatic::APPROVED);
        $user = Auth::user();

        $subscribedParentList = $user->parentsSubscribed()
            ->with([
                'parent' => function($q){
                    $q->select(['id', 'name', 'email', 'avatar']);
                    $q->where('status', UserStatusStatic::ACTIVE);
                },
                'parent.parentInformation'
            ])
            ->where('status', $status)
            ->get();
            
        return response()
            ->json($subscribedParentList);
    }

    public function approveParentSubscribe($id)
    {
        $parentSubscribe = ParentStudent::where('id', $id)
            ->where('student_id', Auth::id())
            ->first();

        if ($parentSubscribe) {
            $parentSubscribe->status = ParentStudentStatusStatic::APPROVED;
            $parentSubscribe->save();
        }

        return response()
            ->json($parentSubscribe);
    }

    public function rejectParentSubscribe($id)
    {
        $parentSubscribe = ParentStudent::where('id', $id)
            ->where('student_id', Auth::id())
            ->first();

        if ($parentSubscribe) {
            $parentSubscribe->status = ParentStudentStatusStatic::REJECTED;
            $parentSubscribe->save();
        }

        return response()
            ->json($parentSubscribe);
    }
}

?>