<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Product;
use App\Enum\NormalizeMode;
use App\Service\Common\CollectionService;
use App\Validation\Product\CreateProductValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService extends BaseService
{

    private MediaService $mediaService;

    function __construct(
        EntityManagerInterface $em,
        Security $security,
        ValidatorInterface $validator,
        CollectionService $collectionService,
        MediaService $mediaService
    )
    {
        parent::__construct($em, $security, $validator, $collectionService);
        $this->mediaService = $mediaService;
    }

    public function get($id): Product
    {
        return parent::_getObject($id, Product::class);
    }

    public function filter($filters, $start = 0, $limit = 10, $orderBy = null) {
        return $this->em->getRepository(Product::class)->filter($filters, $start, $limit, $orderBy);
    }

    public function create(array $data) {
        $this->_validate(new CreateProductValidation(), $data);

        $obj = new Product();
        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function update(string $id, array $data) {
        $this->_validate(new CreateProductValidation(), $data);

        $obj = $this->get($id);

        $obj->fromArray($this->collectionService, $data);

        $this->em->persist($obj);
        $this->em->flush();

        return $obj;
    }

    public function delete(string $id): bool
    {
        $productImages = $this->em->getRepository(Media::class)->findBy([
            'type' => Media::TYPE_PRODUCT,
            'relatedEntity' => Product::class,
            'relatedId' => $id
        ], ['id' => 'ASC']);

        foreach ($productImages as $media) {
            $this->mediaService->deleteMedia($media);
        }
        return parent::_deleteObject($id, Product::class);
    }

    public function uploadProductsImages($id, array $imagesInfo, $files) {
        $product = $this->get($id);

        foreach ($imagesInfo as $imageInfo) {
            $file = $files->get($imageInfo['key']);
            $this->mediaService->processMedia(
                @$imageInfo['action'],
                $file,
                Media::TYPE_PRODUCT,
                Product::class,
                $product->getId(),
                @$imageInfo['id']
            );
        }

        return true;
    }

    public function getProductImages(Product|string $product) {
        $productId = $product instanceof Product ? $product->getId() : $product;
        return $this->mediaService->getImages($productId);
    }

}
