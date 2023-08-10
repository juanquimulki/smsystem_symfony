<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

abstract class BaseController extends AbstractController
{
    public function getToken($user) : string
    {
        $issuedAt = time();
        // valid for 30 days
        $expirationTime = $issuedAt + 30 * (60 * 60 * 24);

        $payload = [
            "user_id" => $user->getId(),
            "user_name" => $user->getName(),
            "user_email" => $user->getEmail(),
            "exp" => $expirationTime
        ];
        
        return JWT::encode($payload, $this->getParameter('app.secret'), 'HS256');
    }

    public function allowAccess(?string $token) : bool
    {
        try {
            if ($token) {
                $decoded = JWT::decode($token, new Key($this->getParameter('app.secret'), 'HS256'));
                return true;
            } else {
                return false;
            }
        } catch (InvalidArgumentException $e) {
            // provided key/key-array is empty or malformed.
            return false;
        } catch (DomainException $e) {
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
            return false;
        } catch (SignatureInvalidException $e) {
            // provided JWT signature verification failed.
            return false;
        } catch (BeforeValidException $e) {
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
            return false;
        } catch (ExpiredException $e) {
            // provided JWT is trying to be used after "exp" claim.
            return false;
        } catch (UnexpectedValueException $e) {
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
            return false;
        }
    }

    public function invalidTokenMessage() {
        return [
            "message" => "Invalid token",
        ];
    }
}
