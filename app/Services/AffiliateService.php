<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        if ($email === null) {
            throw new AffiliateCreateException();
        }

        $userInExisting = User::where('email', $email)->first();
        if ($userInExisting !== null && $userInExisting->type == User::TYPE_MERCHANT) {
            throw new AffiliateCreateException();
        }

        if ($userInExisting !== null && $userInExisting->type == User::TYPE_AFFILIATE) {
            throw new AffiliateCreateException();
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE
        ]);

        $discountCode = $this->apiService->createDiscountCode($merchant);
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode['code'] 
        ]);

        Mail::to($email)
            ->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
