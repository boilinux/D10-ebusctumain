<?php

namespace Drupal\custom_module\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;

class CustomApi
{
    public function post_data_log(Request $request)
    {
        $jsonDataResponse = new \stdClass();
        $message = '';
        $messageCode = 200;

        try {
            $requestToken = $request->headers->get('Authorization');
            $adminUser = User::load(1);
            $restToken = $adminUser->field_rest_token->value;

            if ($requestToken == $restToken) {
                $message = 'You have already created attendance for today.';
            } else {
                $message = 'Not registered.';
            }
        } catch (\Exception $error) {
            $message = $error;
            $messageCode = 404;
        }

        $jsonResponse = new JsonResponse(['message' => $message, 'status' => $messageCode, 'method' => 'POST', 'data' => $jsonDataResponse]);

        \Drupal::logger('custom_module')->info($jsonResponse);

        return $jsonResponse;
    }
}
