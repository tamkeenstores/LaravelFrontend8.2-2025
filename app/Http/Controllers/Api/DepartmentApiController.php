<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Section;
use App\Models\DepartmentSections;
use App\Models\DepartmentTeams;
use App\Models\User;
use App\Traits\CrudTrait;
use Illuminate\Support\Facades\DB;


class DepartmentApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'department';
    protected $relationKey = 'department_id';
    
    public function model() {
        $data = ['limit' => -1, 'model' => Department::class, 'sort' => ['id','desc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        return ['teams_data' => 'teamsData', 'sections_data' => 'sectionData', 'manager_data' => 'ManagerData', 'supervisor_data' => 'SupervisorData'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [
            'users' => User::where('role_id', '!=', 2)->get(['id as value', DB::raw("CONCAT(phone_number, ' - ', first_name, ' ', last_name) AS label")]),
            'sections' => Section::where('status', 1)->get(['id as value', 'name as label'])
        ];
    }
    
    public function store(Request $request) {
        $success = false;
        $message = '';
        $department = Department::create([
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'branch_status' => isset($request->branch_status) ? $request->branch_status : 0,
            'branch' => isset($request->branch) ? $request->branch : null,
            'section_status' => isset($request->section_status) ? $request->section_status : 0,
            'manager_status' => isset($request->manager_status) ? $request->manager_status : 0,
            'manager' => isset($request->manager) ? $request->manager : null,
            'supervisor_status' => isset($request->supervisor_status) ? $request->supervisor_status : 0,
            'supervisor' => isset($request->supervisor) ? $request->supervisor : null,
            'team_status' => isset($request->team_status) ? $request->team_status : 0,
        ]);
        
        if (isset($request->section)) {
            foreach($request->section as $key => $sec) {
                $sections = [
                    'department_id' => $department->id,
                    'section_id' => $sec['section_id'],
                ];
                DepartmentSections::create($sections);
            }
        }
        
        if (isset($request->team)) {
            foreach($request->team as $k => $team) {
                $teams = [
                    'department_id' => $department->id,
                    'team_id' => $team['team_id'],
                ];
                DepartmentTeams::create($teams);
            }
        }
        $success = true;
        $message = 'Department Has been created!';
        
        $response = [
            'success' => $success, 
            'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function update(Request $request, $id) {
        $success = false;
        $message = '';
        
        if (isset($request->section)) {
            $resdata = DepartmentSections::where('department_id', '=',$id)->get();
            $resdata->each->delete();
            
            foreach($request->section as $key => $sec) {
                $sections = [
                    'department_id' => $id,
                    'section_id' => $sec['section_id'],
                ];
                DepartmentSections::create($sections);
            }
        }
        
        if (isset($request->team)) {
            $ddata = DepartmentTeams::where('department_id', '=',$id)->get();
            $ddata->each->delete();
            
            foreach($request->team as $k => $team) {
                $teams = [
                    'department_id' => $id,
                    'team_id' => $team['team_id'],
                ];
                DepartmentTeams::create($teams);
            }
        }
        
        $department = Department::whereId($id)->update([
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'branch_status' => isset($request->branch_status) ? $request->branch_status : 0,
            'branch' => isset($request->branch) ? $request->branch : null,
            'section_status' => isset($request->section_status) ? $request->section_status : 0,
            'manager_status' => isset($request->manager_status) ? $request->manager_status : 0,
            'manager' => isset($request->manager) ? $request->manager : null,
            'supervisor_status' => isset($request->supervisor_status) ? $request->supervisor_status : 0,
            'supervisor' => isset($request->supervisor) ? $request->supervisor : null,
            'team_status' => isset($request->team_status) ? $request->team_status : 0,
        ]);
        
        $success = true;
        $message = 'Department Has been update!';
        
        $response = [
            'success' => $success, 
            'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
