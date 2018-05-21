<?php
/**
 * Created by PhpStorm.
 * User: sl
 * Date: 2018/5/21
 * Time: 上午9:50
 * @author April2 <ott321@yeah.net>
 */

namespace Swoft\Auth\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\App;
use Swoft\Auth\AuthUserService;
use Swoft\Auth\Constants\ServiceConstants;
use Swoft\Auth\Exception\AuthException;
use Swoft\Auth\Helper\ErrorCode;
use Swoft\Http\Message\Middleware\MiddlewareInterface;

/**
 * Class AclMiddleware
 * @package Swoft\Auth\Middleware
 *
 */
class AclMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttributes()['requestHandler'][2]['handler'] ?? '';
        $handlerArray = self::getHandlerArray($requestHandler);
        if ($requestHandler && is_array($handlerArray)) {
            /** @var AuthUserService $service */
            $service = App::getBean(ServiceConstants::AUTH_USERS_SERVICE);
            $flag = $service->auth($handlerArray[0], $handlerArray[1]);
            if (!$flag) {
                throw new AuthException(ErrorCode::ACCESS_DENIED);
            }
        }
        $response = $handler->handle($request);
        return $response;
    }

    /**
     * @param string $handler
     * @return array|null
     */
    public static function getHandlerArray(string $handler)
    {
        $segments = explode('@', trim($handler));
        if (!isset($segments[1])) {
            return null;
        }
        return $segments;
    }

}