<?php

namespace App\Controller\Backend;

use App\Controller\BaseController;
use App\Enum\NormalizeMode;
use App\Service\CategoryService;
use App\Service\Common\CollectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/v1")]
class CategoryController extends BaseController
{
    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    #[Route("/categories", methods: ["POST"])]
    public function create(Request $request, CategoryService $categoryService): Response
    {
        $data = json_decode($request->getContent(), true);
        $r = $categoryService->create($data);
        return parent::_response($r, NormalizeMode::BASIC, 201);
    }

    #[Route("/categories/{id}", methods: ["PUT"])]
    public function update($id, CategoryService $categoryService, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $r = $categoryService->update($id, $data);

        return parent::_response($r);
    }

    #[Route("/categories", methods: ["GET"])]
    public function list(CategoryService $categoryService): Response
    {
        $r = $this->collectionService->collectionToArray($categoryService->list());
        return parent::_response($r);
    }


    #[Route("/categories/{id}", methods: ["GET"])]
    public function get(CategoryService $categoryService, $id)
    {
        return parent::_response($categoryService->get($id));
    }
}
