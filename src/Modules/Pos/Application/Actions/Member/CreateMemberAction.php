<?php

namespace Modules\Pos\Application\Actions\Member;

use Modules\Pos\Application\DTO\MemberData;
use Modules\Pos\Domain\Contracts\MemberRepositoryInterface;
use Modules\Pos\Domain\Entities\Member;

class CreateMemberAction
{
    public function __construct(
        private MemberRepositoryInterface $repository,
    ) {}

    public function execute(int $outletId, MemberData $data): Member
    {
        return $this->repository->create($outletId, $data);
    }
}
