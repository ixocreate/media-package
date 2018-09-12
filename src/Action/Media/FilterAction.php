<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Action\Media;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Media\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Assert\Assertion;
use Zend\Diactoros\Response\JsonResponse;
use KiwiSuite\Admin\Response\ApiListResponse;

final class FilterAction implements MiddlewareInterface
{
    private $uri;

    private $mediaRepository;

    private $whiteList;

    private $possibleOptions = [
      'image',
      'document',
      'audio',
      'video'
    ];

    public function __construct(MediaRepository $mediaRepository, MediaConfig $mediaConfig, Uri $uri)
    {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
        $this->whiteList = $mediaConfig->whitelist();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Assertion::isJsonString($request->getBody()->getContents())) {
            return new ApiErrorResponse('data_need_to_be_json');
        }

        if (empty($request->getBody()->getContents())) {
            return new ApiErrorResponse('no_parameters_passed_to_filter');
        }

        $requestData = json_decode($request->getBody()->getContents(), true);

        if (!in_array($requestData['filter'], $this->possibleOptions)) {
            return new ApiErrorResponse('given_filter_option_is_not_valid');
        }

        $result = [];
        switch ($requestData['filter']) {
            case 'image':
                foreach ($this->whiteList['image'] as $extension => $mimeType) {
                    $result[] = $this->mediaRepository->findBy(array('mimeType' => $mimeType));
                }
                break;
            case 'document':
                foreach ($this->whiteList['application'] as $extension => $mimeType) {
                    $result[] = $this->mediaRepository->findBy(array('mimeType' => $mimeType));
                }
                foreach ($this->whiteList['text'] as $extension => $mimeType) {
                    $result[] = $this->mediaRepository->findBy(array('mimeType' => $mimeType));
                }
                break;
            case 'audio':
                foreach ($this->whiteList['audio'] as $extension => $mimeType) {
                    $result[] = $this->mediaRepository->findBy(array('mimeType' => $mimeType));
                }
                break;
            case 'video':
                foreach ($this->whiteList['video'] as $extension => $mimeType) {
                    $result[] = $this->mediaRepository->findBy(array('mimeType' => $mimeType));
                }
                break;
        }

        $apiListResult = $handler->handle($request);

        if (!($apiListResult instanceof ApiListResponse)) {
            return $apiListResult;
        }

        $items = $apiListResult->items();
        foreach ($items as $key => $value) {
            $items[$key]['thumb'] = $this->uri->generateImageUrl($result['basePath'], $result['filename'], 'admin-thumb');
            $items[$key]['original'] = $this->uri->generateImageUrl($result['basePath'], $result['filename']);
        }

        return new ApiListResponse(
            $apiListResult->resource(),
            $items,
            $apiListResult->meta()
        );
    }
}