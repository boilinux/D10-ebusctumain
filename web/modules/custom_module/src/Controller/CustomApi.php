<?php

namespace Drupal\custom_module\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\node\Entity\Node;
// use Drupal\file\Entity\File;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom api.
 */
class CustomApi {
	public function post_data_log(Request $request) {
		$jsonDataResponse = new \stdClass();
		$message = '';
		$messageCode = 200;
        private $QRCODE_LIMIT_PER_SECONDS = 10;

		try {
			$requestToken = $request->headers->get('Authorization');
			$adminUser = User::load(1);
			$restToken = $adminUser->field_rest_token->value;

			if ($requestToken == $restToken) {

				$passengerStatus = "";
				$passengerType = "";
				$seatAvailable = 0;
				$latitude = "";
				$longitude = "";

				// Create node for data log
				$bodyData = Json::decode($request->getContent());

				// from flutter app
				if (!empty($bodyData['qrcode'])) {
					$strQrcode = $bodyData['qrcode'];
					if (in_array($strQrcode, ['student', 'senior_citizen', 'regular'])) {
						$passengerType = $bodyData['qrcode'];
						$passengerStatus = "get_off_the_bus";
						$latitude = $bodyData['latitude'];
						$longitude = $bodyData['longitude'];

						// easpek
						exec("sudo espeak-ng \"Hello Driver, Passenger want to get off the bus.\" -ven-us+f3 -s150 ");
						$queryData = \Drupal::database()->query(
							"SELECT nfbsa.field_bus_seat_available_value AS seat_available, nfbc.field_bus_capacity_value AS capacity, nfd.created AS created FROM node_field_data AS nfd
                            LEFT JOIN node__field_bus_seat_available AS nfbsa ON nfbsa.entity_id = nfd.nid
                            LEFT JOIN node__field_bus_capacity AS nfbc ON nfbc.entity_id = nfd.nid
                            WHERE nfd.type = 'data_logs'
                            ORDER BY nfd.nid DESC
                            LIMIT 1"
						)->fetchAll();

						if (empty($queryData)) {
							// no passenger records
							$messageCode = 404;
							$message = "No passenger record";
							$jsonResponse = new JsonResponse(['message' => $message, 'status' => $messageCode, 'method' => 'POST', 'data' => $jsonDataResponse]);

							\Drupal::logger('custom_module')->info($jsonResponse);

							return $jsonResponse;
						}

						// add state and oldstate for 1 time add qrcode scanner
						$oldTimestamp = $queryData[0]->created;
						$newTimestamp = \Drupal::time()->getCurrentTime();

						if ($newTimestamp - $oldTimestamp < $QRCODE_LIMIT_PER_SECONDS) {
							// detect error scanning qrcode
							$messageCode = 404;
							$message = "qrcode scanned already multiple times";
							$jsonResponse = new JsonResponse(['message' => $message, 'status' => $messageCode, 'method' => 'POST', 'data' => $jsonDataResponse]);

							\Drupal::logger('custom_module')->info($jsonResponse);

							return $jsonResponse;
						}
						//...

						$oldPassengerSeat = $queryData[0]->seat_available;
						$busCapacity = $queryData[0]->capacity;

						$seatAvailable = $oldPassengerSeat + 1; // increment new available seat
						// easpek
						exec("sudo espeak-ng \"Avaialbe seat now, is " . $seatAvailable . ".\" -ven-us+f3 -s150 ");

					}
				} else {
					$queryData = \Drupal::database()->query(
						"SELECT nfbsa.field_bus_seat_available_value AS seat_available, nfbc.field_bus_capacity_value AS capacity FROM node_field_data AS nfd
                        LEFT JOIN node__field_bus_seat_available AS nfbsa ON nfbsa.entity_id = nfd.nid
                        LEFT JOIN node__field_bus_capacity AS nfbc ON nfbc.entity_id = nfd.nid
                        WHERE nfd.type = 'data_logs'
                        ORDER BY nfd.nid DESC
                        LIMIT 1"
					)->fetchAll();

					if (!empty($queryData)) {
						$jsonResponse = new JsonResponse($queryData);
						\Drupal::logger('custom_module')->info($jsonResponse);

						$oldPassengerSeat = $queryData[0]->seat_available;
						$busCapacity = $queryData[0]->capacity;
						if ($oldPassengerSeat > 0) {
							$seatAvailable = $oldPassengerSeat - 1;
						} else {
							// no available seat
							// disable printing of receipt
							$messageCode = 404;
							$message = "No available seat";
							$jsonResponse = new JsonResponse(['message' => $message, 'status' => $messageCode, 'method' => 'POST', 'data' => $jsonDataResponse]);

							\Drupal::logger('custom_module')->info($jsonResponse);

							return $jsonResponse;
						}
					} else {
						$seatAvailable = 23; // 23; because 24 seat capacity
					}
					$passengerStatus = $bodyData['passenger_status'];
					$passengerType = $bodyData['passenger_type'];
				}

				// $date = \DateTime::createFromFormat('d-m-Y H:i:s', $bodyData['date'] . ' 00:00:00');
				// $date = $date->getTimestamp();
				$date = date("F j, Y, g:i a");
				$title = 'Data log for ' . $passengerType . ' - ' . $passengerStatus . ' - ' . $date;

				$node = Node::create([
					'type' => 'data_logs',
					'title' => $title,
					'field_type_of_passenger' => $passengerType,
					'field_passenger_status' => $passengerStatus,
					'field_bus_seat_available' => $seatAvailable,
					'field_location' => ['lat' => $latitude, 'lon' => $longitude, 'name' => 'location', 'zoom' => 15, 'type' => 'roadmap', 'width' => '500px', 'height' => '500px', 'marker' => 1, 'traffic' => 0, 'marker_icon' => '', 'controls' => 1],
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
