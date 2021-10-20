<?php
namespace App\Actions;

use Illuminate\Http\JsonResponse;

/**
 * SendResponse to client
 * @author shellrean <wandinak17@gmail.com>
 * @since 1.0.0
 */
class SendResponse
{
    /**
     * Status 403 Forbidden
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(string $message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'you do not have access to this source'
        ],403);
    }

    /**
     * Status 404 Not Found
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(string $message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'data not found'
        ],404);
    }

    /**
     * Status 400 Bad Request
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function badRequest(string $message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'bad request'
        ],400);
    }

    /**
     * Status 200 Accept
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function accept(string $message = '')
    {
        return response()->json([
            'error' => false,
            'message' => $message != '' ? $message : 'success'
        ],200);
    }

    /**
     * Status 200 Accept data
     *
     * @param mixed $data
     * @return JsonResponse
     */
    public static function acceptData($data)
    {
        return response()->json([
            'error' => false,
            'data' => $data
        ], 200);
    }

    /**
     * Status 200 Accept data custom
     *
     * @param mixed $data
     * @return  JsonResponse
     */
    public static function acceptCustom($data)
    {
        return response()->json($data, 200);
    }

    /**
     * Status 500 Internal server error
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function internalServerError(string $message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'internal server error'
        ],500);
    }
}
