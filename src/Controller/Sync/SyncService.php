<?php

namespace App\Controller\Sync;

use App\Controller\BaseService;
use App\Entity\Category;
use App\Entity\Product;
use App\Service\CollectionService;
use App\Service\HttpClientService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SyncService extends BaseService
{

    private HttpClientService $httpClientService;
    private ParameterBagInterface $parameterBag;

    function __construct(
        EntityManagerInterface $em,
        Security $security,
        ValidatorInterface $validator,
        CollectionService $collectionService,
        HttpClientService $httpClientService,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct($em, $security, $validator, $collectionService);
        $this->httpClientService = $httpClientService;
        $this->parameterBag = $parameterBag;
    }

    public function syncProducts() {
        $info = $this->httpClientService->fetchData($this->parameterBag->get('product_provider_url'));

        $this->syncCategories($info);

        $batchSize = $this->parameterBag->get('batch_size');
        $receivedProductsIds = [];

        foreach ($info['categories'] as $category) {
            $receivedProductsIds = array_merge($receivedProductsIds, array_column($category['products'], 'id'));
        }

        $storedProducts = $this->em->getRepository(Product::class)->getProductsByOriginIds($receivedProductsIds);

        $storedIndex = [];
        foreach ($storedProducts as $product) {
            $storedIndex[$product->getOriginId()] = $product;
        }

        $toAdd = [];
        $toEdit = [];

        foreach ($info['categories'] as $category) {
            foreach ($category['products'] as $product) {
                $editedProduct = $product;
                $editedProduct['originCategory'] = $category['id'];

                if (!isset($storedIndex[$product['id']])) {
                    $toAdd[] = $editedProduct;
                } elseif ($this->collectionService->getStringFromDate($storedIndex[$product['id']]->getOriginUpdate(), 'Y-m-d H:i:s') !== $product['updated'] || $storedIndex[$category['id']]->isDelete()) {
                    $toEdit[] = $editedProduct;
                }
            }
        }

        $i = 0;
        foreach ($toAdd as $product) {
            $obj = new Product();

            $images = [];
            foreach ($product['images'] as $image) {
                $images[] = [
                    'path' => @$image['path'],
                    'thumbnailPath' => @$image['thumbnailPath']
                ];
            }

            $data = [
                'name' => $product['name'],
                'originId' => $product['id'],
                'originUpdated' => $product['updated'],
                'images' => $product['images'],
                'price' => $product['price'],
                'description' => @$product['description']
            ];

            $obj->fromArray($this->collectionService, $data);
            $this->em->persist($obj);
            $i++;
            if ($i % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        foreach ($toEdit as $product) {
            if (isset($storedIndex[$product['id']])) {
                $data = [
                    'name' => $product['name'],
                    'originId' => $product['id'],
                    'originUpdated' => $product['updated'],
                    'images' => $product['images'],
                    'price' => $product['price'],
                    'description' => @$product['description']
                ];

                $obj->fromArray($this->collectionService, $data);
                $obj->setDeleted(null);
                $this->em->persist($obj);

                $i++;
                if ($i % $batchSize === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }

        $this->em->getRepository(Product::class)->softDeleteByIds($receivedProductsIds);
        $this->em->flush();

        return $info;
    }

    private function syncCategories($info) {
        $batchSize = $this->parameterBag->get('batch_size');

        //region process categories
        $receivedCatIds = array_column($info['categories'], 'id');
        $storedCategories = $this->em->getRepository(Category::class)->getCategoriesByOriginIds($receivedCatIds);

        $storedIndex = [];
        foreach ($storedCategories as $category) {
            $storedIndex[$category->getOriginId()] = $category;
        }

        $toAdd = [];
        $toEdit = [];
        foreach ($info['categories'] as $category) {
            if (!isset($storedIndex[$category['id']])) {
                $toAdd[] = $category;
            } elseif ($this->collectionService->getStringFromDate($storedIndex[$category['id']]->getOriginUpdate(), 'Y-m-d H:i:s') !== $category['updated'] || $storedIndex[$category['id']]->isDelete()) {
                $toEdit[] = $category;
            }
        }
        $i = 0;

        foreach ($toAdd as $category) {
            $obj = new Category();
            $data = [
                'name' => $category['name'],
                'originId' => $category['id'],
                'originUpdated' => $category['updated']
            ];

            $obj->fromArray($this->collectionService, $data);
            $this->em->persist($obj);
            $i++;
            if ($i % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        foreach ($toEdit as $category) {
            if (isset($storedIndex[$category['id']])) {
                $obj = $storedIndex[$category['id']];

                $data = [
                    'name' => $category['name'],
                    'originId' => $category['id'],
                    'originUpdated' => $category['updated']
                ];

                $obj->fromArray($this->collectionService, $data);
                $obj->setDeleted(null);
                $this->em->persist($obj);

                $i++;
                if ($i % $batchSize === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }

        $this->em->getRepository(Category::class)->softDeleteByIds($receivedCatIds);
        $this->em->flush();
    }

}
