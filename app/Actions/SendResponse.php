<?php
namespace App\Actions;

class SendResponse
{
    /**
     * 403 Forbidden
     *
     * @return \Illuminate\Http\Response
     */
    public static function forbidden($message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'you do not have access to this source'
        ],403);
    }

    /**
     * 404 Not Found
     *
     * @return \Illuminate\Http\Response
     */
    public static function notFound($message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'data not found'
        ],404);
    }

    /**
     * 400 Bad Request
     *
     * @return \Illuminate\Http\Response
     */
    public static function badRequest($message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'bad request'
        ],400);
    }

    /**
     * 200 Accept
     *
     * @return \Illuminate\Http\Response
     */
    public static function accept($message = '')
    {
        return response()->json([
            'error' => false,
            'message' => $message != '' ? $message : 'success'
        ],200);
    }

    /**
     * 200 Accept data
     *
     * @return \Illuminate\Http\Response
     */
    public static function acceptData($data)
    {
        return response()->json([
            'error' => false,
            'data' => $data
        ], 200);
    }

    /**
     * 200 Accept data custom
     *
     * @return  \Illuminate\Http\Response
     */
    public static function acceptCustom($data)
    {
        return response()->json($data, 200);
    }

    /**
     * 500 Internal server error
     *
     * @return \Illuminate\Http\Response
     */
    public static function internalServerError($message = '')
    {
        return response()->json([
            'error' => true,
            'message' => $message != '' ? $message : 'internal server error'
        ],500);
    }
}
