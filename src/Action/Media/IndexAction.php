<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Action\Media;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Admin\RoleInterface;
use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Media\Uri\MediaUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class IndexAction implements MiddlewareInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaUri
     */
    private $uri;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * IndexAction constructor.
     *
     * @param MediaRepository $mediaRepository
     * @param MediaUri $uri
     * @param MediaConfig $mediaConfig
     */
    public function __construct(
        MediaRepository $mediaRepository,
        MediaUri $uri,
        MediaConfig $mediaConfig
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $criteria = new Criteria();
        $sorting = null;

        //?sort[column1]=ASC&sort[column2]=DESC&filter[column1]=test&filter[column2]=foobar
        $queryParams = $request->getQueryParams();
        foreach ($queryParams as $key => $value) {
            if (\mb_substr($key, 0, 4) === 'sort') {
                $sorting = [];
                foreach ($value as $sortName => $sortValue) {
                    $sorting[$sortName] = $sortValue;
                }
            } elseif (\mb_substr($key, 0, 6) === 'filter') {
                foreach ($value as $filterName => $filterValue) {
                    $criteria->andWhere(Criteria::expr()->contains($filterName, $filterValue));
                }
                continue;
            } elseif ($key === 'offset') {
                $value = (int)$value;
                if (!empty($value)) {
                    $criteria->setFirstResult($value);
                }
                continue;
            } elseif ($key === 'limit') {
                $value = (int)$value;
                if (!empty($value)) {
                    $criteria->setMaxResults(\min($value, 500));
                }
                continue;
            } elseif ($key === 'type') {
                switch ($value) {
                    case 'image':
                        $mimeTypes = $this->mediaConfig->imageWhitelist();
                        break;
                    case 'audio':
                        $mimeTypes = $this->mediaConfig->audioWhitelist();
                        break;
                    case 'video':
                        $mimeTypes = $this->mediaConfig->videoWhitelist();
                        break;
                    case 'document':
                        $mimeTypes = $this->mediaConfig->documentWhitelist();
                        break;
                    default:
                        $mimeTypes = null;
                        break;
                }

                if (!empty($mimeTypes)) {
                    $criteria->andWhere(Criteria::expr()->in('mimeType', $mimeTypes));
                }
            }
        }

        /** @var RoleInterface $role */
        $role = $request->getAttribute(User::class)->role()->getRole();
        if (\in_array('media.only-own-media', $role->getPermissions())) {
            $criteria->andWhere(Criteria::expr()->eq('createdBy', $request->getAttribute(User::class)->id()));
        }

        if (empty($sorting)) {
            $criteria->orderBy(['createdAt' => 'DESC']);
        } elseif (!empty($sorting)) {
            $criteria->orderBy($sorting);
        }

        $items = [];
        $result = $this->mediaRepository->matching($criteria);
        /** @var Media $media */
        foreach ($result as $media) {
            $item = $media->toPublicArray();
            $item['thumb'] = $this->uri->imageUrl($media, 'admin-thumb');
            $item['original'] = $this->uri->url($media);

            $items[] = $item;
        }

        $count = $this->mediaRepository->count($criteria);

        return new ApiSuccessResponse([
            'items' => $items,
            'count' => $count,
        ]);
    }
}
