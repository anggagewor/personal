<?php

namespace Modules\Shared\Infrastructure\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trait for controllers that need to verify resource ownership.
 *
 * Provides a reusable `findOwnedOrFail` method that:
 * 1. Finds the entity by ID via the repository
 * 2. Verifies the entity belongs to the authenticated user
 * 3. Aborts with 403 if ownership check fails
 *
 * Usage:
 *   use AuthorizesOwnership;
 *
 *   $note = $this->findOwnedOrFail($this->repository, $id, $request);
 */
trait AuthorizesOwnership
{
    /**
     * Find an entity by ID and verify it belongs to the authenticated user.
     *
     * @template T of object
     * @param object $repository Repository with a findById(int): ?T method
     * @param int $id Entity ID
     * @param Request $request Current HTTP request (to get authenticated user)
     * @return object The found entity
     *
     * @throws NotFoundHttpException If entity not found
     * @throws AccessDeniedHttpException If entity doesn't belong to user
     */
    protected function findOwnedOrFail(object $repository, int $id, Request $request): object
    {
        $entity = $repository->findById($id);

        if (!$entity) {
            abort(404, 'Data tidak ditemukan.');
        }

        if ($entity->userId !== $request->user()->id) {
            abort(403, 'Kamu tidak punya akses untuk melakukan ini.');
        }

        return $entity;
    }
}
