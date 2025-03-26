<?php
namespace App\Service;

use App\Entity\Media;
use App\Entity\Product;
use App\Enum\NormalizeMode;
use App\Helper\GeneralHelper;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MediaService
{
    const MAX_WIDTH = 900;
    const MAX_HEIGHT = 675;

    const MAX_THUMBNAIL_WIDTH = 340;
    const MAX_THUMBNAIL_HEIGHT = 180;

    private Filesystem $filesystem;
    private EntityManagerInterface $em;
    private ParameterBagInterface $parameterBag;
    private $businessSlug;

    public function __construct(Filesystem $awsS3Storage, EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->filesystem = $awsS3Storage;
        $this->em = $em;
        $this->parameterBag = $parameterBag;
        $this->businessSlug = $this->parameterBag->get('business_slug');
    }

    function processMedia($action, $file, $type, $entity, $relatedId, $id, $width = self::MAX_WIDTH, $height = self::MAX_HEIGHT)
    {
        if ((!empty($action) && !empty($file)) || $action === 'DELETE') {
            switch ($action) {
                case 'ADD':
                    $media = (new Media())
                        ->setType($type)
                        ->setRelatedEntity($entity)
                        ->setRelatedId($relatedId);
                    $this->upload(
                        $media,
                        $file,
                        $width,
                        $height
                    );
                    break;
                case 'UPDATE':
                    if (!empty($id)) {
                        $media = $this->em->getRepository(Media::class)->findOneById($id);
                        if ($media instanceof Media) {
                            $this->upload(
                                $media,
                                $file,
                                $width,
                                $height
                            );
                        }
                    }
                    break;
                case 'DELETE':
                    $media = (new Media())
                        ->setId($id)
                        ->setType($type)
                        ->setRelatedEntity($entity)
                        ->setRelatedId($relatedId);
                    $this->deleteMedia($media);
                    break;
            }
        }
    }

    public function getTempPath($isThumbnail = false): string
    {
        $publicPath = $this->parameterBag->get('app.public_dir');
        $imageTempPath = $publicPath . 'uploads/';
        if (!is_dir($imageTempPath))
            mkdir($imageTempPath);
        if ($isThumbnail) {
            $imageTempPath = $imageTempPath . 'thumb/';
            if (!is_dir($imageTempPath))
                mkdir($imageTempPath);
        }
        return $imageTempPath;
    }

    function getBucketPath(): string {
        $bucketPath = $this->parameterBag->get('s3_bucket_test_path');
        if ($this->parameterBag->get('app_server') === 'prod') {
            $bucketPath = $this->parameterBag->get('s3_bucket_path');
        }
        return $bucketPath;
    }

    function upload(Media $obj, $file, $width = self::MAX_WIDTH, $height = self::MAX_HEIGHT)
    {
        $fileName = GeneralHelper::getFileName($file->getClientOriginalExtension());
        $tempPath = $this->getTempPath();
        $imageTempPath = $tempPath . $fileName;
        try {

            $file->move($this->getTempPath(), $fileName);
            if (!empty($obj->getId())) {
                $this->deleteMedia($obj, false);
            }

            $obj->setPath($fileName);
            if ($obj->getType() === Media::TYPE_PRODUCT) {
                $this->processProductImage($obj, $fileName, $width, $height);
            } else {
                $s3Path = $this->getMediaPath($obj);
                $productImgData = $this->resizeImage($imageTempPath, $width, $height);
                $this->filesystem->write($s3Path, $productImgData);
            }

            $this->em->persist($obj);
            $this->em->flush();

            $this->deleteTempImage($fileName, $obj->getType() === Media::TYPE_PRODUCT);
        } catch (\Exception $e) {
            $this->deleteTempImage($fileName, $obj->getType() === Media::TYPE_PRODUCT);
            throw $e;
        }
    }

    function deleteMedia(Media $obj, $deleteMediaRecord = true)
    {
        $filters = [
            'id' => $obj->getId(),
            'type' => $obj->getType(),
            'relatedId' => $obj->getRelatedId(),
            'relatedEntity' => $obj->getRelatedEntity()
        ];

        $medias = $this->em->getRepository(Media::class)->findBy(
            $filters,
            null,
            1
        );

        if (count($medias) > 0) {
            $mediaToDelete = $medias[0];

            if ($mediaToDelete->getType() === Media::TYPE_PRODUCT) {
                $thumbKey = $this->getMediaPath($mediaToDelete, true);
                $this->filesystem->delete($thumbKey);
            }

            $key = $this->getMediaPath($mediaToDelete);
            $this->filesystem->delete($key);

            if ($deleteMediaRecord) {
                $this->em->remove($mediaToDelete);
                $this->em->flush();
            }
        }
    }

    function getMediaPath(Media|string $objOrName, $isThumbnail = false, $withDomain = false): string
    {
        $bucketPath = $this->getBucketPath();

        $filePath = '';
        if ($objOrName instanceof Media) {
            $pathInBusiness = '';
            switch ($objOrName->getType()) {
                case Media::TYPE_PRODUCT:
                    $pathInBusiness = '/products';
            }

            if ($isThumbnail) {
                $pathInBusiness .= '/thumb';
            }

            $filePath = $pathInBusiness . '/' . $objOrName->getPath();
        } else {
            $filePath = '/' . $objOrName;
        }

        return (($withDomain) ? $this->parameterBag->get('s3_url') : '') . $bucketPath . '/' . $this->businessSlug . $filePath;
    }

    function getImages($relatedId, $type = Media::TYPE_PRODUCT, $relatedEntity = Product::class) {

        $media = $this->em->getRepository(Media::class)->findBy([
            'type' => $type,
            'relatedEntity' => $relatedEntity,
            'relatedId' => $relatedId
        ], ['id' => 'ASC']);

        $images = [];

        foreach ($media as $m) {
            $imagePath = $this->getMediaPath(
                $m,
                false,
                true
            );
            $thumbPath = $this->getMediaPath(
                $m,
                true,
                true
            );

            $image['path'] = $imagePath;
            $image['thumbnailPath'] = $thumbPath;
            $images[] = $image;
        }

        return $images;
    }

    private function deleteTempImage($fileName, $isProductType = false)
    {
        $tempPath = $this->getTempPath();
        $imageTempPath = $tempPath . $fileName;
        unlink($imageTempPath);
        if ($isProductType) {
            unlink($this->getTempPath(true) . $fileName);
        }
    }

    private function resizeImage($imagePath, $width, $height): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);
        $image->orient();

        if ($image->width() > ($width) or $image->height() > ($height)) {
            if ($image->width() > $width) {
                $image->scale(width: $width);
            }

            if ($image->height() > $height) {
                $image->scale(height: $height);
            }
        }

        return (string)$image->encode();
    }

    private function processProductImage(Media $obj, $fileName, $width, $height)
    {
        $imageTempThumbPath = $this->getTempPath(true) . $fileName;
        $imageTempPath = $this->getTempPath() . $fileName;

        copy($imageTempPath, $imageTempThumbPath);

        $productImgData = $this->resizeImage($imageTempPath, $width, $height);
        $this->filesystem->write($this->getMediaPath($obj), $productImgData);

        $productThumbImgData = $this->resizeImage($imageTempThumbPath, self::MAX_THUMBNAIL_WIDTH, self::MAX_THUMBNAIL_HEIGHT);
        $this->filesystem->write($this->getMediaPath($obj, true), $productThumbImgData);
    }
}
