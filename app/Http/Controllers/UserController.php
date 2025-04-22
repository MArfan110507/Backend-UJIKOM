<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    protected $apiResponse;
    protected $logService;
    protected $pathController = 'App\Http\Controllers\\';

    public function __construct()
    {
        $this->apiResponse = app('App\Services\ApiResponseService');
        $this->logService = app('App\Services\LogService');
    }

    public function getAllNotification(Request $request)
    {
        $userLogin = Auth::user();

        $validator = Validator::make($request->all(), [
            'sort_by_read' => 'nullable|in:read,unread',
            'sort_by_created_at' => 'nullable|in:latest,oldest',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->universalError($validator->errors());
        }

        try {
            $user = User::find($userLogin->id);

            if (!$user) {
                return $this->apiResponse->universalError('User not found.', Response::HTTP_NOT_FOUND);
            }

            $query = $user->notifications();

            if ($request->filled('sort_by_read')) {
                $request->sort_by_read === 'read'
                    ? $query->whereNotNull('read_at')
                    : $query->whereNull('read_at');
            }

            if ($request->filled('sort_by_created_at')) {
                $sortDirection = $request->sort_by_created_at === 'latest' ? 'desc' : 'asc';
                $query->orderBy('created_at', $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc'); // default
            }

            $perPage = $request->input('per_page', 15);
            $notifications = $query->paginate($perPage);

            return $this->apiResponse->success(
                'Notifications retrieved successfully.',
                $notifications,
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            $this->logService->saveErrorLog(
                "Error occurred while fetching all notifications",
                $this->pathController . 'UserController:getAllNotification',
                $e
            );

            return $this->apiResponse->internalServerError("An error occurred while fetching all notifications.");
        }
    }
}
