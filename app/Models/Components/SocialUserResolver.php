<?php

namespace App\Components;

// 3rd party
use Adaojunior\Passport\SocialGrantException;
use Adaojunior\Passport\SocialUserResolverInterface;

class SocialUserResolver implements SocialUserResolverInterface
{

    /**
     * Resolves user by given network and access token.
     *
     * @param string $network
     * @param string $accessToken
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function resolve($network, $accessToken, $accessTokenSecret = null)
    {
        switch ($network) {
            case 'facebook':
                return $this->authWithFacebook($accessToken);
                break;
            default:
                throw SocialGrantException::invalidNetwork();
                break;
        }
    }
    
    
    /**
     * Resolves user by facebook access token.
     *
     * @param string $accessToken
     * @return \App\User
     */
    protected function authWithFacebook($accessToken)
    {
        $account = \Socialize::driver('facebook')->userFromToken($accessToken);
        $user = \App\User::where('email', $account->email)->first();
        return $user;
    }
}
