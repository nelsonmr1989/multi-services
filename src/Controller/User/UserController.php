<?php

namespace App\Controller\User;

use App\Controller\BaseController;
use App\Entity\User;
use App\Enum\NormalizeMode;
use App\Service\CollectionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/v1")]
class UserController extends BaseController
{
    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    #[Route("/users/register", methods: ["POST"])]
    public function register(Request $request, UserService $userService): Response
    {
        $data = json_decode($request->getContent(), true);
        $r = $userService->create($data, true);
        return parent::_response($r, NormalizeMode::BASIC, 201);
    }

    #[Route("/users/activate/{code}", name: "users_activate", methods: ["GET"])]
    public function activate($code, UserService $userService, ParameterBagInterface $parameterBag)
    {
        $isActive = $userService->activate($code);
        //TODO: Implement UI in WebApp to introduce the Code
        // return parent::_response($r);

        $url = $parameterBag->get('web_url') . 'auth/login?msg=';
        if ($isActive)
            return new RedirectResponse($url . "Usuario Activado correctamente, ya puede iniciar sesión.&code=0");
        return new RedirectResponse($url . "No se ha podido activar el usuario.&code=1");
    }

    #[Route("/users/{id}", methods: ["PUT"])]
    public function update($id, UserService $userService, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $r = $userService->update($id, $data);

        return parent::_response($r);
    }

    #[Route("/users/{id}", methods: ["DELETE"])]
    public function delete(UserService $userService, $id, Security $security)
    {
        if ($security->getUser()->getRole() != User::ROLE_ADMIN)
            return new JsonResponse([
                'message' => 'Access denied.'
            ], 403);
        // $r = $userService->delete($id);
        return parent::_response($userService->delete($id));
    }
}
