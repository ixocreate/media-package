<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 20.04.18
 * Time: 13:14
 */

namespace KiwiSuite\Media;


use App\Entity\Media;
use App\Repository\MediaRepository;
use Cocur\Slugify\Slugify;
use Intervention\Image\ImageManager;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\CommandBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\UploadedFile;

final class UploadAction implements MiddlewareInterface
{

    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    public function __construct(CommandBus $commandBus, MediaRepository $mediaRepository)
    {
        $this->commandBus = $commandBus;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\array_key_exists('file', $request->getUploadedFiles())) {
            return new ApiErrorResponse("invalid_file");
        }


        $upload = $request->getUploadedFiles()['file'];
        if (!($upload instanceof UploadedFile)) {
            return new ApiErrorResponse("invalid_file");
        }

        do {
            $basePath = \implode('/', str_split(bin2hex(random_bytes(3)), 2)) . '/';
            $exists = is_dir('data/media/' . $basePath);
        } while ($exists === true);

        mkdir('data/media/' . $basePath, 0777, true);
        $filenameParts = pathinfo($upload->getClientFilename());
        $slugify = new Slugify();
        $filename = $slugify->slugify($filenameParts['filename']) . '.' . $filenameParts['extension'];

        $upload->moveTo('data/media/' . $basePath . $filename);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $media = new Media([
            'id' => Uuid::uuid4(),
            'basePath' => $basePath,
            'filename' => $filename,
            'mimeType' => finfo_file($finfo, 'data/media/' . $basePath . $filename),
            'size' => sprintf('%u', filesize('data/media/' . $basePath . $filename)),
            'createdAt' => new \DateTimeImmutable(),
        ]);

        $media = $this->mediaRepository->save($media);

        $this->generateImage($media);

        return new ApiSuccessResponse();
    }

    private function generateImage(Media $media)
    {
        if (substr($media->mimeType(), 0 , 6)  !== 'image/') {
            return;
        }

        if (strpos($media->mimeType(), 'svg') !== false) {
            return;
        }

        $imageManager = new ImageManager(['driver' => 'imagick']);

        mkdir('data/media/img/promotion/' . $media->basePath(), 0777, true);
        $image = $imageManager->make('data/media/' . $media->basePath() . $media->filename());
        $image->resize(680, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $image->save('data/media/img/promotion/' . $media->basePath() . $media->filename());
        $image->destroy();

        mkdir('data/media/img/admin-thumb/' . $media->basePath(), 0777, true);
        $image = $imageManager->make('data/media/' . $media->basePath() . $media->filename());
        $image->fit(500, 500);
        $image->save('data/media/img/admin-thumb/' . $media->basePath() . $media->filename());
        $image->destroy();
    }
}