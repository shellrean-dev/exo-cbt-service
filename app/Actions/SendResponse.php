<?php
namespace App\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
     * @return Response
     */
    public static function forbidden(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'you do not have access to this source'
        ],403, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 404 Not Found
     *
     * @param string $message
     * @return Response
     */
    public static function notFound(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'data not found'
        ],404, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 400 Bad Request
     *
     * @param string $message
     * @return Response
     */
    public static function badRequest(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'bad request'
        ],400, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept
     *
     * @param string $message
     * @return Response
     */
    public static function accept(string $message = '')
    {
        return new Response([
            'error' => false,
            'message' => $message != '' ? $message : 'success'
        ],200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept data
     *
     * @param mixed $data
     * @return Response
     */
    public static function acceptData($data)
    {
        return new Response([
            'error' => false,
            'data' => $data
        ], 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept data custom
     *
     * @param mixed $data
     * @return  Response
     */
    public static function acceptCustom($data)
    {
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 500 Internal server error
     *
     * @param string $message
     * @return Response
     */
    public static function internalServerError(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'internal server error'
        ], 500, [
            'Content-Type' => 'application/json'
        ]);
    }
}
