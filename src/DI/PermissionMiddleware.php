<?php

namespace UMA\FpvJpApi\DI;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class PermissionMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        
        $courseId = $route->getArgument('id');
        
        // do permission logic...
        
        return $handler->handle($request);
    }
}