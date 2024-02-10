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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PermissionMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $accessToken = $this->verifyAccessToken($request);
        // if ($accessToken instanceof Response) {
        //     return $accessToken;
        // }

        $idToken = $this->verifyIdToken($request);
        if ($idToken instanceof Response) {
            return $idToken;
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

    public function getToken(ServerRequestInterface $request): ?string
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

    public function verifyAccessToken(ServerRequestInterface $request): mixed
    {
        $accessToken = $this->getToken($request);
        if ($accessToken == null) {
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => 'Authorization token required']));
        }

        try {
            $client = new Client([
                'base_uri' => 'https://' . $_ENV['AUTH0_DOMAIN'],
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            $response = $client->request('GET', '/userinfo');
        } catch (ClientException $e) {
            error_log('Auth0 Token Validation Error: ' . $e->getMessage());
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => $e->getMessage()]));
        }

        return json_decode($response->getBody(), true);
    }

    public function verifyIdToken(ServerRequestInterface $request): array|Response
    {
        $idToken = $this->getToken($request);
        if ($idToken == null) {
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => 'Authorization token required']));
        }

        try {
            $token = $this->createIdToken($idToken);
            $token->verify();
            $token->validate();
        } catch (InvalidTokenException $e) {
            error_log('Auth0 Token Validation Error: ' . $e->getMessage());
            return new Response(403, ['Content-Type' => 'application/json'], json_encode(['error' => $e->getMessage()]));
        }

        return $token->toArray();
    }

    public function createIdToken(string $jwt): Token
    {
        $auth0Configuration = [
            'domain' => $_ENV['AUTH0_DOMAIN'],
            'clientId' => $_ENV['AUTH0_CLIENT_ID'],
            'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
            'tokenAlgorithm' => $_ENV['AUTH0_TOKEN_ALGORITHM'],
            'cookieSecret' => bin2hex(random_bytes(32)),
        ];
        $config = new SdkConfiguration($auth0Configuration);
        return new Token($config, $jwt, Token::TYPE_ID_TOKEN);
    }

}
