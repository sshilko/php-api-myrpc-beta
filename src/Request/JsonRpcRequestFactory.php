<?php

declare(strict_types = 1);

namespace myrpc\Request;

use JsonException;
use myrpc\Exception\ServiceException;
use function assert;
use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class JsonRpcRequestFactory implements RequestFactoryInterface
{
    public function create(mixed $data = null): RequestInterface
    {
        /**
         * JSON-RPC request object
         * @see https://www.jsonrpc.org/specification#request_object
         *
         * {
         *   "method": "v1/user/facebook#signup",
         *   "params": {"token": "123", "name": "Dave"},
         *   "id": "3021d640-799a-4290-a964-b3924dbad4c1",
         *   "sid" "507f191e810c19729de860ea"
         * }
         *
         * {
         *   "method": "v1/message#post",
         *   "params": {"text": "hello", "name": "Dave"},
         *   "id": "3021d640-799a-4290-a964-b3924dbad4c2",
         *   "sid" "507f191e810c19729de860ea"
         * }
         */
        try {
            $decoded = is_string($data) ? json_decode($data, true, 512, JSON_THROW_ON_ERROR) : [];
            assert(is_array($decoded));
        } catch (JsonException $ex) {
            throw new ServiceException($ex->getMessage());
        }

        $method = isset($decoded['method']) && is_string($decoded['method']) ? $decoded['method'] : null;
        $params = isset($decoded['params']) && is_array($decoded['params']) ? $decoded['params'] : null;
        $rid = isset($decoded['id']) && is_scalar($decoded['id']) ? (string) $decoded['id'] : null;
        $sid = isset($decoded['sid']) && is_scalar($decoded['sid']) ? (string) $decoded['sid'] : null;

        return new JsonRpcRequest($method, $params, $rid, $sid);
    }
}
