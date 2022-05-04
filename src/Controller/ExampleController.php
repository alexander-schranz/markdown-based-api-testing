<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController
{
    #[Route('/api/examples/{id}', methods: ['GET'], name: 'api_example_get')]
    public function getAction(Request $request, int $id): Response
    {
        if (!$request->headers->has('X-Auth-Token')) {
            return new Response('Invalid credentials', Response::HTTP_FORBIDDEN);
        }
        if ('e1f4ec0d-54df-465e-8cf3-78dad2ca8463' !== $request->headers->get('X-Auth-Token')) {
            return new Response('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }
        if ($id !== 1) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse([
            'id' => $id,
            'title' => 'Test',
        ]);
    }

    #[Route('/api/examples', methods: ['POST'], name: 'api_example_post')]
    public function postAction(Request $request): Response
    {
        $id = mt_rand(2, 100);

        /** @var array{title: string} $content */
        $content = \json_decode($request->getContent(), true);

        return new JsonResponse([
            'id' => $id,
            'title' => $content['title'],
        ], 201);
    }
}
