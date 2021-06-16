<?php

declare(strict_types=1);

namespace App\Services;

class PropertiesService
{
    public function getProperties()
    {
        return json_decode(
            file_get_contents('http://grupozap-code-challenge.s3-website-us-east-1.amazonaws.com/sources/source-2.json')
        );
    }

    public function findByZap($pageSize)
    {
        $properties = $this->getProperties();
        $newProperties = array();

        foreach($properties as $key => $property){
            $location = $property->address->geoLocation->location;

            $eligibleLocation = $this->validLocation($location->lon, $location->lat);
            $eligibleByPrices = $this->eligibleByPricesZap($property);
            $elegibleUsableAreas = $this->usableAreas($property);

            if($eligibleLocation && $eligibleByPrices && $elegibleUsableAreas){
                $newProperties[$key] = $property;
            }  
        }

        $totalCount = count($newProperties);
        $pageNumber = round($totalCount/$pageSize);

        $newProperties = [
        'pageNumber' => $pageNumber,
        'pageSize' => $pageSize,
        'totalCount' => $totalCount,
        'listings' => $newProperties
        ];
        
        return "<pre>" . json_encode($newProperties, JSON_PRETTY_PRINT) . "</pre>";

    }

    public function findByViva($pageSize)
    {
        $properties = $this->getProperties();
        $newProperties = array();

        foreach($properties as $key => $property){
            $location = $property->address->geoLocation->location;

            $eligibleLocation = $this->validLocation($location->lon, $location->lat);
            $eligibleByPrices = $this->eligibleByPricesViva($property);
            $eligiblemonthlyCondoFee = $this->eligibleByMonthlyCondoFee($property);

            if($eligibleLocation && $eligibleByPrices && $eligiblemonthlyCondoFee){
                $newProperties[$key] = $property;
            }

        }
        $totalCount = count($newProperties);
        $pageNumber = round($totalCount/$pageSize);

        $newProperties = [
        'pageNumber' => $pageNumber,
        'pageSize' => $pageSize,
        'totalCount' => $totalCount,
        'listings' => $newProperties
        ];
       
        return "<pre>" . json_encode($newProperties, JSON_PRETTY_PRINT) . "</pre>";
    
    }

    private function validLocation($lon, $lat){
        if($lon == 0 || $lat == 0){
            return false;
        }
       return true;
    }

    private function boundingBox($lon, $lat)
    {
        
        if($lon == 0 || $lat == 0){
            return false;
        }

        return (($lon >= -46.693419 && $lon <=-46.641146) || ($lat >= -23.568704 && $lat <= -23.546686));
    
    }

    private function eligibleByPricesZap($property){
        $priceSaleMin = 600000;
        $priceRentalMin = 3500;
        $percent = 10;

        $location = $property->address->geoLocation->location;

        $salePrice = $this->boundingBox($location->lon, $location->lat)
            ? $priceSaleMin - round($priceSaleMin * ($percent / 100), 2) : $priceSaleMin ;
        $rentalPrice = $this->boundingBox($location->lon, $location->lat)
            ? $priceRentalMin - round($priceRentalMin * ($percent / 100), 2) : $priceRentalMin;

        return (
            ($property->pricingInfos->businessType === 'SALE' && $property->pricingInfos->price >= $salePrice)
            ||
            ($property->pricingInfos->businessType === 'RENTAL' && $property->pricingInfos->rentalTotalPrice >= $rentalPrice)
        );
    }

    private function usableAreas($property)
    {
        $squareMeterMin = 3500;

        if ($property->pricingInfos->businessType === 'RENTAL') {
            return true;
        }


        if ($property->usableAreas <= 0) {
            return false;
        }

        if(!is_numeric($property->usableAreas)){
            return false;
        }
        
        $newPrice = $this->boundingBox($property->address->geoLocation->location->lon, $property->address->geoLocation->location->lat) ?
            $property->pricingInfos->price + ($property->pricingInfos->price * (10 / 100)) : $property->pricingInfos->price;

            $squareMeterValue = round(($newPrice / $property->usableAreas), 2);

        return $squareMeterValue > $squareMeterMin;
    }   

    private function eligibleByPricesViva($property){
        $percent = 50;
        $priceSale = 700000;
        $priceRental = 4000;
        
        $location = $property->address->geoLocation->location;

        $newPrice = $this->boundingBox($location->lon, $location->lat) ? $priceRental + ($priceRental * ($percent / 100)) : $priceRental;
        
        return (
            ($property->pricingInfos->businessType === 'SALE' && $property->pricingInfos->price <= $priceSale ) ||
            ($property->pricingInfos->businessType === 'RENTAL' && $property->pricingInfos->rentalTotalPrice <= $newPrice)
        );
    }


    private function eligibleByMonthlyCondoFee($property){
        $pricingInfos = $property->pricingInfos;

        if ($pricingInfos->businessType === 'SALE') {
            return true;
        }

        if (!isset($pricingInfos->monthlyCondoFee) || !is_numeric($pricingInfos->monthlyCondoFee)) {
            return false;
        }

        $valueCondoDescont = $pricingInfos->rentalTotalPrice - (($pricingInfos->rentalTotalPrice * (30 / 100)));
        $maxValueCondo = $pricingInfos->rentalTotalPrice - $valueCondoDescont; 

        return $pricingInfos->monthlyCondoFee < $maxValueCondo;
    }
}