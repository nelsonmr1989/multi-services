<?php

namespace App\Controller\Backend;

use App\Controller\BaseController;
use App\Enum\NormalizeMode;
use App\Service\Common\CollectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RecipientService;

#[Route("/v1")]
class RecipientController extends BaseController
{
    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    #[Route("/recipients", methods: ["POST"])]
    public function create(Request $request, RecipientService $recipientService): Response
    {
        $data = json_decode($request->getContent(), true);
        $r = $recipientService->create($data);
        return parent::_response($r, NormalizeMode::BASIC, 201);
    }

    #[Route("/recipients/{id}", methods: ["PUT"])]
    public function update($id, RecipientService $recipientService, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $r = $recipientService->update($id, $data);

        return parent::_response($r);
    }

    #[Route("/recipients", methods: ["GET"])]
    public function list(RecipientService $recipientService): Response
    {
        $r = $this->collectionService->collectionToArray($recipientService->list());
        return parent::_response($r);
    }

    #[Route("/recipients/{id}", methods: ["DELETE"])]
    public function delete($id, RecipientService $recipientService, Request $request)
    {
        $r = $recipientService->delete($id);
        return parent::_response($r);
    }
}
