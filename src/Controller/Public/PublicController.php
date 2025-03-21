<?php

namespace App\Controller\Public;

use App\Controller\BaseController;
use App\Controller\Category\CategoryService;
use App\Helper\GeneralHelper;
use App\Service\CollectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/v1/pub/rsc")]
class PublicController extends BaseController
{
    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    #[Route("/category/filter", methods: ["POST"])]
    public function filter(CategoryService $categoryService, Request $request)
    {
        $mode = GeneralHelper::parseNormalizeMode($request->get('mode', 'medium'));
        $data = parent::_getDataFilter($request);
        return parent::_response($categoryService->filter($data['filters'], $data['start'], $data['limit'], $data['order_by']), $mode);
    }
}
