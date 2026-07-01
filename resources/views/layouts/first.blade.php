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
        $consumerKey = env('NETSUITE_CONSUMER_KEY');
        $consumerSecret = env('NETSUITE_CONSUMER_SECRET');
        $token = env('NETSUITE_TOKEN_ID');
        $tokenSecret = env('NETSUITE_TOKEN_SECRET');

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

    /**
     * Run a SuiteQL query and automatically follow pagination
     * (NetSuite defaults to 1000 rows per page and returns
     * "hasMore" + a "next" link when more pages exist).
     *
     * Returns the full merged list of items across all pages.
     */
    private function runSuiteQL($sql)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url  = $this->client->getConfig('base_uri') . $path;

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

        $result = json_decode($response->getBody(), true);
        $items  = $result['items'] ?? [];

        // Keep following the "next" link until hasMore is false
        while (!empty($result['hasMore'])) {
            $nextLink = null;

            foreach ($result['links'] ?? [] as $link) {
                if (($link['rel'] ?? null) === 'next') {
                    $nextLink = $link['href'];
                    break;
                }
            }

            if (!$nextLink) {
                break;
            }

            $result = $this->getRawUrl($nextLink);
            $items  = array_merge($items, $result['items'] ?? []);
        }

        return $items;
    }

    public function getVendorBillGLImpact($id)
    {
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

        return $this->runSuiteQL($sql);
    }

    public function getVendorBillWithholdingTax($id)
    {
        $sql = "
            SELECT
                SUM(ABS(custcol_4601_witaxamount)) AS withholdingtax
            FROM transactionLine
            WHERE transaction = $id
            AND mainline = 'F'
        ";

        return $this->runSuiteQL($sql);
    }

    // STEP 1: search vendor bill
    public function searchVendorBill($tranid)
    {
        $sql = "
            SELECT id, tranid, entity, total, trandate
            FROM transaction
            WHERE type = 'VendBill'
            AND (
                tranid LIKE '%$tranid%'
                OR transactionNumber LIKE '%$tranid%'
            )
        ";

        return $this->runSuiteQL($sql);
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

        return $bill;
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

        $result = json_decode($response->getBody(), true);
        $items  = $result['items'] ?? [];

        // Record sublists can also be paged via "hasMore" + "next" link
        while (!empty($result['hasMore'])) {
            $nextLink = null;

            foreach ($result['links'] ?? [] as $link) {
                if (($link['rel'] ?? null) === 'next') {
                    $nextLink = $link['href'];
                    break;
                }
            }

            if (!$nextLink) {
                break;
            }

            $result = $this->getRawUrl($nextLink);
            $items  = array_merge($items, $result['items'] ?? []);
        }

        return $items;
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

        return $this->runSuiteQL($sql);
    }

    public function getGRNGLImpact($itemReceiptId)
    {
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

        return $this->runSuiteQL($sql);
    }

    public function getVendorBillCreator($id)
    {
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

        return $this->runSuiteQL($sql);
    }

}