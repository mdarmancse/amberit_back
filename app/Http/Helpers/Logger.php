<?php
namespace App\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;
class Logger
{
    public static function createLog($logName,$action, $model, $requestData = [],$oldData=[])
    {

        $subjectType = 'App\\Models\\' . ucfirst($model);
        $event = strtolower($action);

        $causerId = Auth::guard('sanctum')->id();

        $routeInfo = [
            'action' => Route::currentRouteAction(),
            'url' => request()->url(),
            'method' => request()->method(),
        ];

        $properties = [
            'ip_address' => request()->ip(),
            'route' => $routeInfo,
        ];

        if (in_array($action, ['update', 'delete','read'])) {
            if ($model === 'SecRolePermission'){
                $subjectId = $requestData['id'];
                $subject = $subjectType::where('role_id',$subjectId)->get();
            }
            else{
                $subjectId = $requestData['id'];
                $subject = $subjectType::find($subjectId);
            }




            if ($action == 'delete') {
                $description = 'Deleted ' . ucfirst($model);
            } elseif ($action == 'read'){
                $description = $logName;

            } else {
                $description = 'Updated ' . ucfirst($model);
                if ($subject !== null) {
                    if ($model === 'SecRolePermission'){
                        $newData = $subject;
                    }else{
                        $newData = $subject->getAttributes();
                    }

                }

                $properties['old_data'] = $oldData??[];
                $properties['new_data'] = $newData??[];
            }
        }

        if ($action == 'create') {
            $description = 'Created ' . ucfirst($model);
            $newData = $requestData;
            $properties['new_data'] = $newData;
        }


        $activity = Activity::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => (new $subjectType)->getTable(),
            'subject_id' => $subjectId ?? null,
            'causer_type' => 'users',
            'causer_id' => $causerId,
            'event' => $event,
            'properties' => $properties,
        ]);

        return $activity;

    }

}
