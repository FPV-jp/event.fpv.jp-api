<?php declare(strict_types=1);

namespace FpvJp\DI;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Token;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class PermissionMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $bearer = $this->getBearer($request);
        if ($bearer == null) {
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => 'Authorization token required']));
        }

        try {
            // Token::TYPE_ACCESS_TOKEN
            // Token::TYPE_ID_TOKEN
            $this->validateToken($bearer, Token::TYPE_ACCESS_TOKEN);
        } catch (InvalidTokenException $e) {
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => 'Auth0 Token Validation Error: ' . $e->getMessage()]));
        }

        // error_log('nickname: ' . $idToken['nickname']);
        // error_log('email: ' . $idToken['email']);
        // error_log('sub: ' . $idToken['sub']);

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $courseId = $route->getArgument('id');

        // パーミッションロジックを実行...

        return $handler->handle($request);
    }

    public function getBearer(ServerRequestInterface $request): ?string
    {
        if ($request->hasHeader('Authorization')) {
            $authorizationHeader = $request->getHeaderLine('Authorization');
            $tokenParts = explode(' ', $authorizationHeader);
            if (count($tokenParts) === 2 && $tokenParts[0] === 'Bearer') {
                return $tokenParts[1];
            }
        }
        return null;
    }

    public function validateToken(string $jwt, int $type)
    {
        $auth0Configuration = [
            'domain' => $_ENV['AUTH0_DOMAIN'],
            'clientId' => $_ENV['AUTH0_CLIENT_ID'],
            'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
            'tokenAlgorithm' => $_ENV['AUTH0_TOKEN_ALGORITHM'],
            'cookieSecret' => bin2hex(random_bytes(32)),
        ];
        $config = new SdkConfiguration($auth0Configuration);
        $token = new Token($config, $jwt, $type);
        $token->verify();
        $token->validate();
        return $token;
    }

}
