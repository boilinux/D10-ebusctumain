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
                // Create node for data log
                $bodyData = Json::decode($request->getContent());

                $passengerStatus = $bodyData['passenger_status'];
                $passengerType = $bodyData['passenger_type'];
                // $date = \DateTime::createFromFormat('d-m-Y H:i:s', $bodyData['date'] . ' 00:00:00');
                // $date = $date->getTimestamp();
                $date = date("F j, Y, g:i a");
                $title = 'Data log for ' . $passengerType . ' - ' . $passengerStatus . ' - ' . $date;

                $node = Node::create([
                    'type' => 'data_logs',
                    'title' => $title,
                    'field_type_of_passenger' => $passengerType,
                    'field_passenger_status' => $passengerStatus,
                    'uid' => 0,
                ]);

                $node->save();

                $message = 'Successfully created data log for ' . $title;
            } else {
                // Invalid credentials
                $message = 'Invalid Credentials';
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
