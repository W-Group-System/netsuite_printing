<?php

namespace App\Services;

use GuzzleHttp\Client;

class NetSuiteService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://' . config('services.netsuite.account') . '.suitetalk.api.netsuite.com',
        ]);
    }

    private function buildOAuthHeader($method, $url)
    {
        $consumerKey = config('services.netsuite.consumer_key');
        $consumerSecret = config('services.netsuite.consumer_secret');
        $token = config('services.netsuite.token_id');
        $tokenSecret = config('services.netsuite.token_secret');

        $nonce = bin2hex(random_bytes(16));
        $timestamp = time();

        $baseString = $method . '&' . rawurlencode($url) . '&' . rawurlencode(
            "oauth_consumer_key=$consumerKey&" .
            "oauth_nonce=$nonce&" .
            "oauth_signature_method=HMAC-SHA256&" .
            "oauth_timestamp=$timestamp&" .
            "oauth_token=$token&" .
            "oauth_version=1.0"
        );

        $signingKey = rawurlencode($consumerSecret) . '&' . rawurlencode($tokenSecret);

        $signature = base64_encode(hash_hmac('sha256', $baseString, $signingKey, true));

        return 'OAuth ' .
            'oauth_consumer_key="' . $consumerKey . '", ' .
            'oauth_token="' . $token . '", ' .
            'oauth_signature_method="HMAC-SHA256", ' .
            'oauth_timestamp="' . $timestamp . '", ' .
            'oauth_nonce="' . $nonce . '", ' .
            'oauth_version="1.0", ' .
            'realm="' . config('services.netsuite.account') . '", ' .
            'oauth_signature="' . rawurlencode($signature) . '"';
    }

    public function getVendorBillGLImpact($id)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

       $sql = "
            SELECT
                tal.account,
                a.acctnumber,
                a.fullname,
                tal.debit,
                tal.credit
            FROM TransactionAccountingLine tal
            LEFT JOIN account a
                ON a.id = tal.account
            WHERE tal.transaction = '$id'
            AND (tal.debit IS NOT NULL OR tal.credit IS NOT NULL)
        ";

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('POST', $url),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Prefer'        => 'transient',
            ],
            'json' => [
                'q' => $sql
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
    public function getVendorBillWithholdingTax($id)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

        $sql = "
            SELECT
                SUM(ABS(custcol_4601_witaxamount)) AS withholdingtax
            FROM transactionLine
            WHERE transaction = $id
            AND mainline = 'F'
        ";

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('POST', $url),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Prefer'        => 'transient',
            ],
            'json' => [
                'q' => $sql
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    // STEP 1: search vendor bill
    public function searchVendorBill($tranid)
    {
        // $url = $this->client->getConfig('base_uri') . '/services/rest/query/v1/suiteql';
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

        // $tranid = addslashes($tranid);

        $response = $this->client->post('/services/rest/query/v1/suiteql', [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('POST', $url),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Prefer' => 'transient',
            ],
            'json' => [
                'q' => "SELECT id, tranid, entity, total, trandate
                        FROM transaction
                        WHERE type = 'VendBill'
                        AND (
                            tranid LIKE '%$tranid%'
                            OR transactionNumber LIKE '%$tranid%'
                        )"
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    // STEP 2: get full vendor bill
    public function getVendorBillRecord($id)
    {
        $url = $this->client->getConfig('base_uri') . "/services/rest/record/v1/vendorBill/$id";

        $response = $this->client->get("/services/rest/record/v1/vendorBill/$id", [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        $bill = json_decode($response->getBody(), true);

        $poIds = [];

        foreach ($bill['item'] ?? [] as $line) {
            if (!empty($line['orderDoc'])) {
                $poIds[] = $line['orderDoc'];
            }
        }
        return json_decode($response->getBody(), true);
    }

    public function getVendorBillItems($id)
    {
        $path = "/services/rest/record/v1/vendorbill/$id/item";
        $url = $this->client->getConfig('base_uri') . $path;

        $response = $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getPurchaseOrder($id)
    {
        $path = "/services/rest/record/v1/purchaseOrder/$id";
        $url = $this->client->getConfig('base_uri') . $path;

        $response = $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getRawUrl($url)
    {
        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getItemReceiptsByPO($poId)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

        $sql = "
            SELECT DISTINCT
                t.id,
                t.tranid,
                t.transactionNumber
            FROM transaction t
            JOIN transactionLine tl ON tl.transaction = t.id
            WHERE t.type = 'ItemRcpt'
            AND tl.createdfrom = $poId
        ";

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('POST', $url),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Prefer'        => 'transient',
            ],
            'json' => [
                'q' => $sql
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getGRNGLImpact($itemReceiptId)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

        $sql = "
            SELECT
                tal.account,
                a.acctnumber,
                a.fullname,
                tal.debit,
                tal.credit,
                tal.transaction
            FROM TransactionAccountingLine tal
            LEFT JOIN account a
                ON a.id = tal.account
            WHERE tal.transaction = $itemReceiptId
            AND (tal.debit IS NOT NULL OR tal.credit IS NOT NULL)
        ";

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('POST', $url),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Prefer'        => 'transient',
            ],
            'json' => [
                'q' => $sql
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getVendorBillCreator($id)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

        $sql = "
            SELECT
                t.createdby,
                e.firstname,
                e.lastname
            FROM transaction t
            LEFT JOIN employee e
                ON e.id = t.createdby
            WHERE t.id = $id
        ";

        $response = $this->client->post($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('POST', $url),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Prefer'        => 'transient',
            ],
            'json' => [
                'q' => $sql
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

}