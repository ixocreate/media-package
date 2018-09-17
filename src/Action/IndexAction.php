<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare(strict_types=1);

namespace KiwiSuite\Media\Action;

use KiwiSuite\Admin\Response\ApiListResponse;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

final class IndexAction implements MiddlewareInterface
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * IndexAction constructor.
     * @param Uri $uri
     */
    public function __construct(Uri $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $apiListResult = $handler->handle($request);
        if (!($apiListResult instanceof ApiListResponse)) {
            return $apiListResult;
        }
        $items = $apiListResult->items();
        foreach ($items as $key => $value) {
            $items[$key]['thumb'] = $this->uri->generateImageUrl($value['basePath'], $value['filename'], 'admin-thumb');
            $items[$key]['original'] = $this->uri->generateImageUrl($value['basePath'], $value['filename']);
        }

        return new ApiListResponse(
            $apiListResult->resource(),
            $items,
            $apiListResult->meta()
        );



    }
}
