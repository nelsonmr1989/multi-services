<?php

namespace App\Controller\Backend;

use App\Controller\BaseController;
use App\Controller\WarehouseProduct\WarehouseProductService;
use App\Enum\NormalizeMode;
use App\Helper\GeneralHelper;
use App\Service\Common\CollectionService;
use App\Service\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/v1")]
class ProductController extends BaseController
{
    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    #[Route("/products", methods: ["POST"])]
    public function create(Request $request, ProductService $productService): Response
    {
        $data = json_decode($request->getContent(), true);
        $r = $productService->create($data);
        return parent::_response($r, NormalizeMode::BASIC, 201);
    }

    #[Route("/products/{id}", methods: ["PUT"])]
    public function update($id, ProductService $productService, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $r = $productService->update($id, $data);

        return parent::_response($r);
    }

    #[Route("/products/{id}", methods: ["GET"])]
    public function get(ProductService $productService, $id)
    {
        return parent::_response($productService->get($id));
    }

    #[Route("/products/filter", methods: ["POST"])]
    public function filter(ProductService $productService, Request $request)
    {
        $mode = GeneralHelper::parseNormalizeMode($request->get('mode', 'medium'));
        $data = parent::_getDataFilter($request);
        $products = $this->collectionService->collectionToArray($productService->filter($data['filters'], $data['start'], $data['limit'], $data['order_by']));
        return parent::_response($products);
    }

    #[Route("/products/{id}", methods: ["DELETE"])]
    public function delete(ProductService $productService, $id)
    {
        return parent::_response($productService->delete($id));
    }
}
