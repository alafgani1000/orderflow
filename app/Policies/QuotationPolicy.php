<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function view(User $user, Quotation $quotation): bool
    {
        return $user->getStoreOwnerId() === $quotation->user_id;
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->getStoreOwnerId() === $quotation->user_id;
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->getStoreOwnerId() === $quotation->user_id && $user->isOwner();
    }

    public function convert(User $user, Quotation $quotation): bool
    {
        return $user->getStoreOwnerId() === $quotation->user_id && $quotation->canBeConverted();
    }
}
