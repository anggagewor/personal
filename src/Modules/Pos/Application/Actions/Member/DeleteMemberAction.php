<?php

namespace Modules\Pos\Application\Actions\Member;

use Modules\Pos\Domain\Contracts\MemberRepositoryInterface;

class DeleteMemberAction
{
    public function __construct(
        private MemberRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
