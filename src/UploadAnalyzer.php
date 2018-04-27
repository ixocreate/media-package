<?php
/**
 * Created by PhpStorm.
 * User: afriedrich
 * Date: 24.04.18
 * Time: 08:29
 */

namespace KiwiSuite\Media;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\UploadedFile;

class UploadAnalyzer
{

    private $upload;

    public function __construct(ServerRequestInterface $request)
    {
        $this->upload = $request->getUploadedFiles();
        $this->analyzeUpload();
    }

    private function analyzeUpload()
    {
        if (!\array_key_exists('file', $this->upload)) {
            return new ApiErrorResponse('invalid_file');
        }
        $file = $this->upload['file'];

        if (!($file instanceof UploadedFile)) {
            return new ApiErrorResponse('invalid_file');
        }
        return true;
    }
}