<?php

namespace App\Controller;

use App\Enum\NormalizeMode;
use App\Interfaces\IJsonArray;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected $collectionService = '';

    public function _getDataFilter(Request $request)
    {
        $body = json_decode($request->getContent(), true);

        $page = isset($body['pagination']['page']) ? $body['pagination']['page'] : 1;
        $count = isset($body['pagination']['count']) ? $body['pagination']['count'] : 10;
        return [
            'filters' => isset($body['filters']) ? $body['filters'] : [],
            'order_by' => isset($body['order_by']) ? $body['order_by'] : null,
            'start' => ($page - 1) * $count,
            'limit' => $count
        ];
    }

    protected function _response($data, $mode = NormalizeMode::BASIC, $statusCode = 200, $headers = []) {
        $response = $data;
        if ($data instanceof IJsonArray) {
            $response = $data->toArray($this->collectionService, $mode);
        }

        return new JsonResponse($response, $statusCode, $headers);
    }
}
