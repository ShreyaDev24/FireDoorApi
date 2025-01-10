<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CommonHelpers;

use JWTAuth;
use App\Models\Item;
use App\Models\ItemMaster;
use App\Models\SurveyInfo;
use App\Models\SurveyTasks;
use App\Models\SurveyChangerequest;
use App\Models\Project;
use App\Models\Users;
use App\Models\SurveyStatus;
use App\Models\SurveyAttachment;
use App\Models\Notification;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Socialite;
use Hash;
use Illuminate\Validation\Rule;
use File;

class SurveylistController extends Controller
{

    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function surveyList(Request $request)
    {
        dd('hjb');
        if ($request->status == "completed") {
            $status = 2;
        }
        if ($request->status == "scheduled") {
            $status = 1;
        }
        $userId = JWTAuth::user()->id;
        dd($userId);
        // if (!empty($request->projectId) && $request->projectId == null) {
        $surveyInfo = SurveyInfo::join('project', 'project.id', 'survey_info.projectId')
            ->join('users', 'users.id', 'survey_info.userId')
            ->Join('quotation', 'project.quotationId', 'quotation.id')
            ->Join('quotation_versions', 'project.versionId', 'quotation_versions.id')
            ->select('users.UserJobtitle','project.AddressLine1','project.AddressLine2','project.AddressLine3','survey_info.*','quotation.QuotationGenerationId','quotation_versions.version','project.ProjectName')
            ->where('survey_info.status', $status)
            ->where('survey_info.userId', $userId)
            ->get();
        // }
        // else
        // {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Project Id can not be null!!',
        //         'data' => '',
        //     ], Response::HTTP_NOT_FOUND);
        // }

        $masterList = [];
        if (!empty($surveyInfo) && $surveyInfo->count() > 0) {
            foreach ($surveyInfo as $row) {

                $totalcount = Project::join("quotation_versions", function ($join) {
                        $join->on("quotation_versions.id", "=", "project.versionId")
                            ->on("quotation_versions.quotation_id", "=", "project.quotationId");
                    })
                    ->join('quotation_version_items', 'quotation_version_items.version_id', 'quotation_versions.id')
                    ->join('item_master', 'quotation_version_items.itemmasterID', 'item_master.id')
                    ->select('item_master.*')->where('project.id', $row->projectId)->get()->count();

                $list = [];
                $list['id'] = $row->id;
                $list['project_id'] = $row->projectId;
                $list['scheduledTime'] = $row->fromTime;
                $list['doorSets'] =  $totalcount;

                $list['project_name'] = $row->ProjectName;
                $list['quotation_name'] = $row->QuotationGenerationId.'-'.$row->version;

                $list['status'] = $request->status;
                $list['buildingName'] = '';
                $list['location'] =  $row->AddressLine1 . '' . $row->AddressLine2 . '' . $row->AddressLine3;
                $list['jobType'] = $row->UserJobtitle;
                $list['jobDescription'] = '';
                $masterList[] = $list;
                // dd($row);
            }
            return response()->json([
                'success' => true,
                'message' => 'Survey List fetched successfully!!',
                'data' => $masterList,

            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'There are no survey assigned to this user!!',

            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function surveyDetail(Request $request)
    {
        $userId = JWTAuth::user()->id;
        if (!empty($request->id)) {
            $surveyInfo = SurveyInfo::Join('project', 'project.id', 'survey_info.projectId')
                ->Join('users', 'users.id', 'survey_info.userId')
                ->Join('quotation', 'project.quotationId', 'quotation.id')
                ->Join('quotation_versions', 'project.versionId', 'quotation_versions.id')


                // ->join("quotation_version_items", function ($join) {
                //     $join->on("quotation_version_items.version_id", "=", "survey_info.versionId");

                // })
                // ->join('item_master', 'quotation_version_items.itemmasterID', 'item_master.id')
                ->select('users.FirstName', 'users.LastName', 'users.UserJobtitle', 'project.AddressLine1', 'project.AddressLine2', 'project.AddressLine3', 'project.BuildingType','project.latitude','project.longitude', 'survey_info.*','project.ProjectName', 'quotation.QuotationGenerationId','quotation_versions.version')
                ->where('survey_info.id', $request->id)
                ->where('survey_info.userId', $userId)
                ->get()->first();

            // dd($surveyInfo);

            if (!empty($surveyInfo->companyId) && !empty($surveyInfo->companyId)) {
                $surveyAttachment = SurveyAttachment::where('companyId', $surveyInfo->companyId)->where('projectId', $surveyInfo->projectId)->get();
            }
            else{
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
            if (!empty($surveyInfo)) {

                $totalcount = Project::join("quotation_versions", function ($join) {
                        $join->on("quotation_versions.id", "=", "project.versionId")
                            ->on("quotation_versions.quotation_id", "=", "project.quotationId");
                    })
                    ->join('quotation_version_items', 'quotation_version_items.version_id', 'quotation_versions.id')
                    ->join('item_master', 'quotation_version_items.itemmasterID', 'item_master.id')
                    ->select('item_master.*')->where('project.id', $surveyInfo->projectId)->get()->count();


                $list = [];
                $list['scheduledTime'] = $surveyInfo->fromTime;
                $list['doorSets'] =  $totalcount;

                if ($surveyInfo->status == 2) {
                    $status = "completed";
                }
                if ($surveyInfo->status == 1) {
                    $status = "scheduled";
                }

                if ($surveyInfo->surveyStartStatus == 2) {
                    $surveyStartStatus = "Survey Started";
                }
                if ($surveyInfo->surveyStartStatus == 1) {
                    $surveyStartStatus = "Survey Not Started";
                }

                $list['project_id'] = $surveyInfo->projectId;
                $list['project_name'] = $surveyInfo->ProjectName;
                $list['quotation_name'] = $surveyInfo->QuotationGenerationId.'-'.$surveyInfo->version;

                $list['status'] = $status;
                $list['surveyStatus'] = $surveyStartStatus;
                $list['worker'] = $surveyInfo->FirstName . ' ' . $surveyInfo->LastName;
                $list['buildingName'] = '';
                $list['location'] =  $surveyInfo->AddressLine1 . '' . $surveyInfo->AddressLine2 . '' . $surveyInfo->AddressLine3;
                $list['latitude'] = $surveyInfo->latitude;
                $list['longitude'] = $surveyInfo->longitude;
                $list['jobType'] = $surveyInfo->UserJobtitle;
                $list['jobDescription'] = '';

                foreach ($surveyAttachment as $attach) {
                    $list['attachments'][] = 'https://dev.jfds.co.uk/public/Survey_attachment/'.$attach->attachment;
                }

                $list['notes'] = $surveyInfo->notes;
                $list['signoff']['supervisorName'] = $surveyInfo->supervisorName;
                if($surveyInfo->signatureimage){
                    $list['signoff']['signatureImage'] = url("/".$surveyInfo->signatureimage);
                }
                else{
                    $list['signoff']['signatureImage'] = $surveyInfo->signatureimage;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Survey Detail fetched successfully!!',
                    'data' => $list,

                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No records found!!',
                    'data' => '',
                ], Response::HTTP_NOT_FOUND);
            }
        }
        else{

            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function doorDetail(Request $request)
    {
        $item = Item::join('item_master', 'items.itemId', 'item_master.itemID')
        ->join('survey_status', 'survey_status.itemMasterId', 'item_master.id')
        ->where('survey_status.itemMasterId', $request->door_id)->first();

        // dd($item);
        if (!empty($item)) {
            $itemList = Item::where('items.itemId',$item->itemId)->first();
            $itemData = array();
            $itemData['doorName'] = $item->DoorType;
            $itemData['doorImage'] = $item->SvgImage;
            $itemData['doorStyle'] = '';

            if($item->status == 1){
                $doorStatus = "Pending";
            }
            elseif($item->status == 2){
                $doorStatus = "OnGoing";
            }
            else{
                 $doorStatus = "Completed";
            }

            $itemData['config']['doorset'] = $item->DoorsetType;
            $itemData['config']['handling'] = $item->Handing;
            $itemData['config']['latched'] = $item->LatchType;
            $itemData['mainOptions'] = $itemList;
            // $itemData['mainOptions']['soHeight'] = $item->SOHeight;
            // $itemData['mainOptions']['soWidth'] = $item->SOWidth;
            // $itemData['mainOptions']['soDepth'] = $item->SOWallThick;
            // $itemData['mainOptions']['leafWidth'] = $item->LeafWidth1;
            // $itemData['mainOptions']['leafHeight'] = $item->LeafHeight;
            // $itemData['mainOptions']['fireRating'] = $item->FireRating;
            // $itemData['mainOptions']['hingeType'] = '';
            // $itemData['mainOptions']['frameWidth'] = $item->FrameWidth;
            // $itemData['mainOptions']['frameMaterial'] = $item->FrameMaterial;
            // $itemData['mainOptions']['swing'] = $item->SwingType;
            // $itemData['mainOptions']['lockerType'] = '';
            // $itemData['mainOptions']['concealedCloser'] = '';
             $itemData['doorStatus'] = $doorStatus;

            $data['type'] = 'success';
            $data['message'] = 'sucessful response';
            $data['data'] = $itemData;

            return response()->json([
                'success' => true,
                'data' => $data,

            ], Response::HTTP_CREATED);
        } else {
            $data['type'] = 'error';
            $data['message'] = 'error response';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function currentTask(Request $request)
    {
        $projectId = $request->projectId;
        $user = JWTAuth::user()->id;
        if (!empty($projectId)) {
            $SurveyTasks = SurveyTasks::join('survey_info', 'survey_info.projectId', 'survey_tasks.projectId')->where('survey_tasks.projectId', $projectId)->where('survey_tasks.status', 1)->where('survey_info.userId', $user)->select('survey_tasks.*', 'survey_info.toTime')->get();
            if (!empty($SurveyTasks)) {
                $data = array();
                $i = 0;
                foreach ($SurveyTasks as $tasks) {
                    if ($tasks->status == 1) {
                        $status = 'Pending';
                    } else {
                        $status = 'Completed';
                    }
                    $data[$i]['id'] = $tasks->id;
                    $data[$i]['task'] = $tasks->tasks;
                    $data[$i]['taskStatus'] = $status;
                    $data[$i]['taskDueDateTime'] = $tasks->toTime;
                    $i++;
                }
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = $data;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Task Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Project Id Missing!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function completedTask(Request $request)
    {
        $projectId = $request->projectId;
        $user = JWTAuth::user()->id;
        if (!empty($projectId)) {
            $SurveyTasks = SurveyTasks::join('survey_info', 'survey_info.projectId', 'survey_tasks.projectId')->where('survey_tasks.projectId', $projectId)->where('survey_tasks.status', 2)->where('survey_info.userId', $user)->select('survey_tasks.*', 'survey_info.toTime')->get();
            if (!empty($SurveyTasks)) {
                $data = array();
                $i = 0;
                foreach ($SurveyTasks as $tasks) {
                    if ($tasks->status == 1) {
                        $status = 'Pending';
                    } else {
                        $status = 'Completed';
                    }
                    $data[$i]['id'] = $tasks->id;
                    $data[$i]['task'] = $tasks->tasks;
                    $data[$i]['taskStatus'] = $status;
                    $data[$i]['taskDueDateTime'] = $tasks->toTime;
                    $i++;
                }
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = $data;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Task Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Project Id Missing!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function completedTaskMark($id, Request $request)
    {
        if (!empty($id)) {
            $SurveyTasks = SurveyTasks::find($id);
            if (!empty($SurveyTasks)) {
                $SurveyTasks->status = 2;
                $SurveyTasks->save();
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = 'Status Updated Successfully!';

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function startSurvey(Request $request)
    {
        $itemMasterId = $request->door_id;
        if (!empty($itemMasterId)) {
            $ItemMaster = SurveyStatus::where('itemMasterId',$itemMasterId)->first();

            if (!empty($ItemMaster)) {
                if($ItemMaster->status == 1){
                    $ItemMaster->status = 2;
                    $ItemMaster->save();
                    $response['type'] = 'success';
                    $response['message'] = 'sucessful response';
                    $response['data'] = 'Survey start Successfully!';

                    return response()->json([
                        'success' => true,
                        'data' => $response,

                    ], Response::HTTP_CREATED);
                }
                elseif($ItemMaster->status == 2){
                    $data['type'] = 'error';
                    $data['message'] = 'Survey already started!';

                    return response()->json([
                        'success' => false,
                        'data' => $data
                    ], Response::HTTP_NOT_FOUND);
                }
                elseif($ItemMaster->status == 3){
                    $data['type'] = 'error';
                    $data['message'] = 'Can not change status ,Survey already Completed!';

                    return response()->json([
                        'success' => false,
                        'data' => $data
                    ], Response::HTTP_NOT_FOUND);
                }


            } else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function doorList(Request $request){
        // dd($request->survey_id);
        $user = JWTAuth::user()->id;
        // $data = '';
        // $SurveyInfo = Project::join('survey_info','project.id','survey_info.projectId')
        // ->join("quotation_versions",function($join){
        //     $join->on("quotation_versions.id","=","project.versionId")
        //         ->on("quotation_versions.quotation_id","=","project.quotationId");
        // })
        // ->join('quotation_version_items','quotation_version_items.version_id','quotation_versions.id')
        // ->join('item_master','quotation_version_items.itemmasterID','item_master.id')->join('items','items.itemId','item_master.itemID')
        // ->select('item_master.*','items.*')->where('survey_info.userId',$user)->where('survey_info.id',$request->survey_id)->orderBy('item_master.floor','ASC')->get();
        $parameter= Crypt::encrypt($request->survey_id);
        // $url = 'http://127.0.0.1:8000/project/floorPlanList/'.$parameter;
        $url = 'https://dev.jfds.co.uk/project/floorPlanList/'.$parameter;
        $SurveyInfo = SurveyInfo::join('survey_status','survey_status.projectId','survey_info.projectId')
        ->join('item_master','item_master.id','survey_status.itemMasterId')
        ->join('items','items.itemId','item_master.itemID')
        ->where('survey_info.userId',$user)
        ->select('item_master.*','items.*','survey_status.status')
        ->where('survey_info.id',$request->survey_id)->get();

        // dd($SurveyInfo);

        if(!empty($SurveyInfo) && $SurveyInfo->count() > 0){

            $i = 0;
            $floor = SurveyInfo::join('floor','floor.projectId','survey_info.projectId')->where('survey_info.userId',$user)->where('survey_info.id',$request->survey_id)->select('floor.*')->get();
            // dd($floor->count());
            $data['FloorPlan'] = $url;
            if(!empty($floor) && $floor->count() > 0){
                foreach($floor as $floor){

                    $data[$i]['id'] = $floor->id;
                    $data[$i]['floorName'] = $floor->floor_name;
                    foreach($SurveyInfo as $info){
                    // dd($info);
                        if($floor->floor_name == $info->floor){
                            if($info->status == 1){
                                $status = 'Pending';
                            }else if($info->status == 2){
                                $status = 'OnGoing';
                            }else{
                                $status = 'Completed';
                            }
                            // $data[$i][$j]['doorList']['id'] = $info->itemID;
                            // $data[$i][$j]['doorList']['doorName'] = $info->DoorType;
                            // $data[$i][$j]['doorList']['doorStatus'] = $status;
                            $array = [
                                'id'=> $info->id,//itemMasterId
                                'itmeId'=> $info->itemID,
                                'doorName'=> $info->DoorType,
                                'doorStatus'=> $status,

                            ];
                            $data[$i]['doorList'][] = $array;
                        }
                    }

                    $i++;
                }
            }
            else{
                $data['type'] = 'error';
                $data['message'] = 'Floor value can not be null!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }


            $response['type'] = 'success';
            $response['message'] = 'sucessful response';
            $response['data'] = $data;

            return response()->json([
                'success' => true,
                'data' => $response,

            ], Response::HTTP_CREATED);
        }else{
            $data['type'] = 'error';
            $data['message'] = 'There is no door in this survey!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function signoff(Request $request){
        $surveyId = $request->surveyId;
        $credentials = $request->only(
            "supervisorName",
            "signatureimage"
        );
        $validator = Validator::make($credentials, [
            'supervisorName' => 'required|string',
            'signatureimage' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => False, 'message' => $validator->messages()],  Response::HTTP_BAD_REQUEST);
        }

        if (!empty($surveyId)) {
            $surveyInfo = SurveyInfo::find($surveyId);

            if (!empty($surveyInfo)){
                if ($surveyInfo->status == 1){
                    $surveyInfo->supervisorName = $request->supervisorName;
                    $surveyInfo->signatureimage = $request->signatureimage;

                    if(!empty($request->signatureimage)){
                        $folderPath = 'uploads/signatureimage/';
                        $image_base64 = $request->request->get('signatureimage');
                        $imageName = uniqid(rand(), true) .'_'. time().'_'.uniqid(rand(), true);
                        $file_upload = file_upload($folderPath, $image_base64, $imageName);
                        $surveyInfo->signatureimage = $folderPath.$file_upload;
                    }

                    $surveyInfo->status = 2;
                    $surveyInfo->save();
                    $response['type'] = 'success';
                    $response['message'] = 'sucessful response';
                    $response['data'] = 'Signed off Successfully!';

                    return response()->json([
                        'success' => true,
                        'data' => $response,

                    ], Response::HTTP_CREATED);
                }
                else {
                    $data['type'] = 'error';
                    $data['message'] = 'Survey is not scheduled !';

                    return response()->json([
                        'success' => false,
                        'data' => $data
                    ], Response::HTTP_NOT_FOUND);
                }
            }
            else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }


    public function updateNote(Request $request){
        $surveyId = $request->surveyId;
        $credentials = $request->only(
            "notes"
        );
        $validator = Validator::make($credentials, [
            'notes' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => False, 'message' => $validator->messages()],  Response::HTTP_BAD_REQUEST);
        }

        if (!empty($surveyId)) {
            $surveyInfo = SurveyInfo::find($surveyId);

            if (!empty($surveyInfo)){

                $surveyInfo->notes = $request->notes;


                $surveyInfo->save();
                $data = $request->notes;
                $response['type'] = 'success';
                $response['message'] = 'Note updated Successfully!!';
                $response['data']['notes'] = $data;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);

            }
            else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function getNotifications(Request $request){
        $user = JWTAuth::user()->id;
        if(!empty($user)){

            $Notification = Notification::where('surveyUserId',$user)->orderBy('id','DESC')->get();
            if(!empty($Notification) && $Notification->count() > 0 ){
                $i = 0;
                foreach($Notification as $info){

                    if($info->seenStatus == '1'){
                        $status = 0;
                    }else{
                        $status = 1 ;
                    }
                    $data[$i]['id'] = $info->id;
                    $data[$i]['message'] = $info->message;
                    $data[$i]['dateTime'] = date('F d, h:m a', strtotime($info->created_at));
                    $data[$i]['seenStatus'] = $status;
                    $data[$i]['surveyId'] = $info->surveyInfoId;
                    $data[$i]['jobId'] = $info->surveyUserId;
                    $data[$i]['projectId'] = $info->projectId;
                    $i++;
                }
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = $data;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            }else{
                $data['type'] = 'error';
                $data['message'] = 'Notifications Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        }else{
            $data['type'] = 'error';
            $data['message'] = 'User not found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function unreadNotifications(Request $request){
        $user = JWTAuth::user()->id;
        if(!empty($user)){

            $Notification = Notification::where('surveyUserId',$user)->where('seenStatus',1)->orderBy('id','DESC')->get();
            if(!empty($Notification) && $Notification->count() > 0 ){
                $i = 0;
                foreach($Notification as $info){

                    if($info->seenStatus == '1'){
                        $status = 0;
                    }else{
                        $status = 1;
                    }
                    $data[$i]['id'] = $info->id;
                    $data[$i]['message'] = $info->message;
                    $data[$i]['dateTime'] = date('F d, h:m a', strtotime($info->created_at));
                    $data[$i]['seenStatus'] = $status;
                    $data[$i]['surveyId'] = $info->surveyInfoId;
                    $data[$i]['jobId'] = $info->surveyUserId;
                    $data[$i]['projectId'] = $info->projectId;
                    $i++;
                }
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = $data;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            }else{
                $data['type'] = 'error';
                $data['message'] = 'Notifications Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        }else{
            $data['type'] = 'error';
            $data['message'] = 'User not found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function todayNotifications(Request $request){
        $user = JWTAuth::user()->id;
        if(!empty($user)){

            $Notification = Notification::where('surveyUserId',$user)->whereDate('created_at', Carbon::today())->orderBy('id','DESC')->get();
            if(!empty($Notification) && $Notification->count() > 0 ){
                $i = 0;
                foreach($Notification as $info){

                    if($info->seenStatus == '1'){
                        $status = 0;
                    }else{
                        $status = 1;
                    }
                    $data[$i]['id'] = $info->id;
                    $data[$i]['message'] = $info->message;
                    $data[$i]['dateTime'] = date('F d, h:m a', strtotime($info->created_at));
                    $data[$i]['seenStatus'] = $status;
                    $data[$i]['surveyId'] = $info->surveyInfoId;
                    $data[$i]['jobId'] = $info->surveyUserId;
                    $data[$i]['projectId'] = $info->projectId;
                    $i++;
                }
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = $data;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            }else{
                $data['type'] = 'error';
                $data['message'] = 'Notifications Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        }else{
            $data['type'] = 'error';
            $data['message'] = 'User not found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function markRead($id, Request $request)
    {
        if (!empty($id)) {
            $Notification = Notification::find($id);
            if (!empty($Notification)) {
                $Notification->seenStatus = 2;
                $Notification->save();
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = 'Status Updated Successfully!';

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function confrimDoor(Request $request)
    {
        $itemMasterId = $request->door_id;
        if (!empty($itemMasterId)) {
            $ItemMaster = SurveyStatus::where('itemMasterId',$itemMasterId)->first();
            if (!empty($ItemMaster)) {
                $ItemMaster->status = 3;
                $ItemMaster->save();
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = 'Survey Completed Successfully!';

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function editDoor(Request $request)
    {
        if (!empty($request->door_id)) {

            $surveystatus = SurveyStatus::where('itemMasterId',$request->door_id)->first();

            if(!empty($surveystatus)){
                $SurveyChangerequest = SurveyChangerequest::where('itemMasterId',$surveystatus->itemMasterId)->first();


                if(!empty($SurveyChangerequest)){
                    $SurveyChangerequest->SOWidth = $request->SOWidth;
                    $SurveyChangerequest->SOHeight = $request->SOHeight;
                    $SurveyChangerequest->SODepth = $request->SODepth;
                    $SurveyChangerequest->status = 1;
                    $SurveyChangerequest->save();
                }
                else{

                    $user = Users::where('id',JWTAuth::user()->id)->first();
                    $SurveyChangerequest = new SurveyChangerequest();
                    $SurveyChangerequest->projectId = $surveystatus->projectId;
                    $SurveyChangerequest->userId = JWTAuth::user()->id;
                    $SurveyChangerequest->companyId = $user->CreatedBy;
                    $SurveyChangerequest->itemId = $surveystatus->itemId;
                    $SurveyChangerequest->itemMasterId = $surveystatus->itemMasterId;
                    $SurveyChangerequest->SOWidth = $request->SOWidth;
                    $SurveyChangerequest->SOHeight = $request->SOHeight;
                    $SurveyChangerequest->SODepth = $request->SODepth;
                    $SurveyChangerequest->save();
                }

                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = 'Request send Successfully!';

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            }
            else{
                $response['type'] = 'error';
                $response['message'] = 'Door not found in this survey!';

                return response()->json([
                    'success' => false,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            }

        }
        else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function startSurveyAll(Request $request){
        $surveyId = $request->surveyId;
        if (!empty($surveyId)) {
            $SurveyInfo = SurveyInfo::where('id',$surveyId)->first();
            if (!empty($SurveyInfo)) {
                $SurveyInfo = SurveyInfo::where('projectId',$SurveyInfo->projectId)->get();
                foreach($SurveyInfo as $surveyInfo){
                    $info = SurveyInfo::find($surveyInfo->id);
                    $info->surveyStartStatus = 2;
                    $info->save();
                }
                $data['type'] = 'success';
                $data['message'] = 'Survey Start successfully!';

                return response()->json([
                    'success' => true,
                    'data' => $data
                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Id Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'Id Not Found!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function companyDetails(Request $request){
        $userId = JWTAuth::user()->id;
        if (!empty($userId)) {
            $surveyId = Users::where('users.id',$userId)->first();
            $user = Users::join('companies', 'users.id', 'companies.UserId')->where('users.UserType', 2)->where('users.id', $surveyId->CreatedBy)->select('users.UserEmail', 'users.FirstName', 'users.LastName', 'users.UserImage', 'users.UserPhone', 'users.UserJobtitle','companies.*', 'users.id')->first();
            if (!empty($user)) {
                $user['UserImage'] = 'https://dev.jfds.co.uk/CompanyLogo/'. $user->UserImage;
                $response['type'] = 'success';
                $response['message'] = 'sucessful response';
                $response['data'] = $user;

                return response()->json([
                    'success' => true,
                    'data' => $response,

                ], Response::HTTP_CREATED);
            } else {
                $data['type'] = 'error';
                $data['message'] = 'Company Not Found!';

                return response()->json([
                    'success' => false,
                    'data' => $data
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $data['type'] = 'error';
            $data['message'] = 'User Id Missing!';

            return response()->json([
                'success' => false,
                'data' => $data
            ], Response::HTTP_NOT_FOUND);
        }

    }
}
