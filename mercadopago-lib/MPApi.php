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

}
