<?php

declare(strict_types=1);

namespace App\Controller\StaticFiles;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetTeamLogo extends Base
{
    /**
     * @param array<string> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {

        $teamId = $args['filename'];
        $path = $_ENV['STATIC_IMAGE_FOLDER'] . 'teams/' . $teamId . '.png';;
        // . '.png';
    
        if (!file_exists($path)) {
            return $response->withStatus(404);
        }
    
        $type = mime_content_type($path);
        $stream = new \Slim\Http\Stream(fopen($path, 'r'));

        return $response
            ->withHeader('Content-Type', $type)
            ->withHeader('Content-Length', filesize($path))
            ->withHeader('Cache-Control', 'public, max-age=86400')
            ->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT')
            ->withBody($stream);
    }
}
