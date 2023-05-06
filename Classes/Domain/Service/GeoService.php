<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use In2code\Lux\Exception\RequestException;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeoService
{
    /**
     * Use openstreetmap to convert an address to geo coordinates
     *
     * @param string $address
     * @return array ['latitude' => 0.0, 'longitude' => 0.0]
     * @throws RequestException
     */
    public function getCoordinatesFromAddress(string $address): array
    {
        $coordinates = [
            'latitude' => 0.0,
            'longitude' => 0.0,
        ];
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $request = $requestFactory->request(
            'https://nominatim.openstreetmap.org/search?format=json&polygon=1&q=' . urlencode($address)
        );
        if ($request->getStatusCode() !== 200) {
            throw new RequestException('Could not connect to nominatim.openstreetmap.org', 1683405955);
        }
        $result = $request->getBody()->getContents();
        if (StringUtility::isJsonArray($result)) {
            $resultArray = json_decode($result, true);
            if (isset($resultArray[0]['lat']) && isset($resultArray[0]['lon'])) {
                $coordinates['latitude'] = (float)$resultArray[0]['lat'];
                $coordinates['longitude'] = (float)$resultArray[0]['lon'];
            }
        }
        return $coordinates;
    }

    /**
     * Calculate distance between 2 geo coordinates (2x lat and lon) in KM
     *
     * @param float $latitude1
     * @param float $longitude1
     * @param float $latitude2
     * @param float $longitude2
     * @return float
     */
    public function calculateDistance(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $earthRadius = 6371; // in KM
        $lat1InRadians = deg2rad($latitude1);
        $lon1InRadians = deg2rad($longitude1);
        $lat2InRadians = deg2rad($latitude2);
        $lon2InRadians = deg2rad($longitude2);

        $deltaLat = $lat2InRadians - $lat1InRadians;
        $deltaLon = $lon2InRadians - $lon1InRadians;

        $angle = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1InRadians) * cos($lat2InRadians)
            * sin($deltaLon / 2) * sin($deltaLon / 2);
        $centralAngle = 2 * atan2(sqrt($angle), sqrt(1 - $angle));
        $distanceInKm = $earthRadius * $centralAngle;

        return round($distanceInKm, 2);
    }
}
