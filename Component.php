<?php
/**
 * Copyright (C) 2015-present Sergii Gamaiunov <hello@webkadabra.com>
 * All rights reserved.
 */
namespace yii\helper\analytics;

use GuzzleHttp\Client;

class Component extends \yii\base\Component
{
    public $trackerId;
	public $defaultCustomerId = '100000001';
	public $currency = 'USD';
    /**
     * Cancel Google Analytics transaction
     * @link https://ga-dev-tools.appspot.com/hit-builder/
     * @link https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#commonhits
     * @link https://support.google.com/analytics/answer/1037443?hl=en
     *
     * @param $model Order
     */
    public function refundOrder($model) {
        $client = new Client(['base_uri' => 'http://www.google-analytics.com']);
        $data  = [
            'v' => 1,
            't' => 'event',
            'tid' => $this->trackerId,
            'cid' => $model->customer_id  ? $model->customer_id : $this->defaultCustomerId,
            'ec' => 'Ecommerce',
            'ea' => 'Refund',
            'ni' => 1,
            'ti' => $model->id,
            'ta' => \Yii::$app->name,
            'tr' => (0 - $model->grand_total),
            'ts' => (0 - $model->total_shipping),
            'pa' => 'refund',
            'cu' => $this->currency,
        ];
        $i = 1;
        foreach ($model->orderItems as $item) {
            $data['pr'.$i.'id'] = $item->product_id;
            $data['pr'.$i.'qt'] = (0 - $item->qty);
            $data['pr'.$i.'pr'] = $item->price;
            $i++;
        }
        $response = $client->request('POST', 'http://www.google-analytics.com/collect', [
            'query' => $data,
            'form_params' => $data,
        ]);
        return $response;
    }
}