<?php

namespace App\Controller\Country;

use App\Controller\BaseController;
use App\Controller\Sync\SyncService;
use App\Enum\NormalizeMode;
use App\Helper\GeneralHelper;
use App\Service\CollectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/v1")]
class CountryController extends BaseController
{
    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    #[Route("/countries/find-all", methods: ["GET"])]
    public function getAll(CountryService $countryService, SyncService $syncService)
    {
//        $countries = $this->collectionService->collectionToArray(
//            $countryService->getAll()
//        );
//
//        return parent::_response($countries);

        return parent::_response($syncService->syncProducts());
    }
}
