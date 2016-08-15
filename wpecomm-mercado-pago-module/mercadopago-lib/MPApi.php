<?php

include_once "mercadopago.php";

class MPApi{

    public function getPaymentMethods($country_id) {
        $response = MPRestClient::get("/sites/$country_id/payment_methods");
        $response = $response['response'];
        return $response;
    }

    public function getCategories() {
        $response = MPRestClient::get("/item_categories");
        $response = $response['response'];
        return $response;
    }

    public function getMe($access_token) {
        $response = MPRestClient::get("/users/me?access_token=" . $access_token);
        return $response;
    }

    public function getCurrencyRatio($from, $to) {
        $currency_obj = MPRestClient::get(
            "/currency_conversions/search?from=" . $from . "&to=" . $to,
            "application/json",
            MPRestClient::API_BASE_URL_ML
        );
        if (isset($currency_obj['response'])) {
            $currency_obj = $currency_obj['response'];
            if (isset($currency_obj['ratio'])) {
                return (float) $currency_obj['ratio'];
            } else {
                return -1;
            }
        } else {
            return -1;
        }
    }

}
