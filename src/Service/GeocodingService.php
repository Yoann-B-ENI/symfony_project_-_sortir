<?php

namespace App\Service;

class GeocodingService
{
    public function geocodeAddress(string $roadNumber, string $roadName, string $zipCode, string $townName): ?array
    {
        try {

            $fullAddress = sprintf(
                '%s %s, %s %s, France',
                $roadNumber ? $roadNumber : '',
                $roadName,
                $zipCode,
                $townName
            );

            $encodedAddress = urlencode($fullAddress);

            $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q={$encodedAddress}";

            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Symfony Location Geocoder/1.0\r\n"
                ]
            ]);


            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);


            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return [
                    'latitude' => (float)$data[0]['lat'],
                    'longitude' => (float)$data[0]['lon']
                ];
            }
        } catch (\Exception $e) {

            return null;
        }

        return null;
    }
}