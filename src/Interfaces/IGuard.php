<?php

namespace App\Interfaces;

use App\Entity\User;

interface IGuard
{
    public function isOwner(User $user): bool;
}
