<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Package\Action\Media;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Admin\Package\Entity\User;
use Ixocreate\Admin\Package\Response\ApiSuccessResponse;
use Ixocreate\Admin\Package\RoleInterface;
use Ixocreate\Media\Package\Config\MediaConfig;
use Ixocreate\Media\Package\Entity\Media;
use Ixocreate\Media\Package\Entity\MediaCreated;
use Ixocreate\Media\Package\Repository\MediaCreatedRepository;
use Ixocreate\Media\Package\Repository\MediaRepository;
use Ixocreate\Media\Package\Uri\Uri;
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
     * @var Uri
     */
    private $uri;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaCreatedRepository
     */
    private $mediaCreatedRepository;

    /**
     * IndexAction constructor.
     * @param MediaRepository $mediaRepository
     * @param Uri $uri
     * @param MediaConfig $mediaConfig
     * @param MediaCreatedRepository $mediaCreatedRepository
     */
    public function __construct(
        MediaRepository $mediaRepository,
        Uri $uri,
        MediaConfig $mediaConfig,
        MediaCreatedRepository $mediaCreatedRepository
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
        $this->mediaConfig = $mediaConfig;
        $this->mediaCreatedRepository = $mediaCreatedRepository;
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
            if (\mb_substr($key, 0, 4) === "sort") {
                $sorting = [];
                foreach ($value as $sortName => $sortValue) {
                    $sorting[$sortName] = $sortValue;
                }
            } elseif (\mb_substr($key, 0, 6) === "filter") {
                foreach ($value as $filterName => $filterValue) {
                    $criteria->andWhere(Criteria::expr()->contains($filterName, $filterValue));
                }
                continue;
            } elseif ($key === "offset") {
                $value = (int) $value;
                if (!empty($value)) {
                    $criteria->setFirstResult($value);
                }
                continue;
            } elseif ($key === "limit") {
                $value = (int) $value;
                if (!empty($value)) {
                    $criteria->setMaxResults(\min($value, 500));
                }
                continue;
            } elseif ($key === "type") {
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
            $createdResult = $this->mediaCreatedRepository->findBy(['createdBy' => $request->getAttribute(User::class)->id()]);
            $mediaCreatedArray = [];
            /** @var MediaCreated $mediaCreated */
            foreach ($createdResult as $mediaCreated) {
                $mediaCreatedArray[] = (string) $mediaCreated->mediaId();
            }

            $criteria->andWhere(Criteria::expr()->in('id', $mediaCreatedArray));
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
