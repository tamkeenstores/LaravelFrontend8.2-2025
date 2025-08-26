<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralEmailJobs;
use App\Models\GeneralEmailTimes;
use App\Traits\CrudTrait;

class EmailJobsApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'door_step_delivery';
    protected $relationKey = 'door_step_delivery_id';


    public function model() {
        $data = ['limit' => -1, 'model' => GeneralEmailJobs::class, 'sort' => ['id','desc']];
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
        return ['jobtimes' => 'emailtimes'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return [];
    }
    
    public function store(Request $request) 
    {
        $type = $request->type;
        $exists = GeneralEmailJobs::where('type', $type)->count();
        if($exists >= 1){
            return response()->json(['success' => false, 'message' => 'Type Already Exists!']);
        }
        $emailjobs = GeneralEmailJobs::create([
            'hours' => isset($request->hours) ? $request->hours : null,
            'type' => isset($request->type) ? $request->type : null,
            'to' => isset($request->to) ? $request->to : null,
            'cc' => isset($request->cc) ? $request->cc : null,
            'bcc' => isset($request->bcc) ? $request->bcc : null,
            'from' => isset($request->from) ? $request->from : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
         if (isset($request->timedata)) {
             foreach ($request->timedata as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'email_job_id' => $emailjobs->id,
                    'start_time' => isset($value['starttime']) ? $value['starttime'] : null,
                    'end_time' => isset($value['endtime']) ? $value['endtime'] : null,
                    'days' => isset($value['days']) ? $value['days'] : null,
                ];
        
                GeneralEmailTimes::create($data);
             }
         }
         return response()->json(['success' => true, 'message' => 'Email Job Has been created!']);
    }
    
    public function update(Request $request, $id) {
        if (isset($request->timedata)) {
            $restriction_data = GeneralEmailTimes::where('email_job_id', '=',$id)->get();
            $restriction_data->each->delete();
            
            foreach ($request->timedata as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'email_job_id' => $id,
                    'start_time' => isset($value['starttime']) ? $value['starttime'] : null,
                    'end_time' => isset($value['endtime']) ? $value['endtime'] : null,
                    'days' => isset($value['days']) ? $value['days'] : null,
                ];
        
                GeneralEmailTimes::create($data);
             }
        }
        
        $emailjobs = GeneralEmailJobs::whereId($id)->update([
            'hours' => isset($request->hours) ? $request->hours : null,
            'type' => isset($request->type) ? $request->type : null,
            'to' => isset($request->to) ? $request->to : null,
            'cc' => isset($request->cc) ? $request->cc : null,
            'bcc' => isset($request->bcc) ? $request->bcc : null,
            'from' => isset($request->from) ? $request->from : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Email Job Has been updated!']);
    }
}
