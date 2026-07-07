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
                ABS(SUM(custcol_4601_witaxamount)) AS withholdingtax
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

    public function searchServiceInvoice($tranid = null, $from = null, $to = null)
    {
        $path = '/services/rest/query/v1/suiteql';
        $url = $this->client->getConfig('base_uri') . $path;

        $where = [
            "t.type = 'CustInvc'",
            "tl.mainline = 'T'",
            "t.subsidiary IN (1,11,50,8,2, 7)",
            "tl.class IN (1,2,5,6,7)"
        ];
        // $where[] = "tl.taxitem IN (1,2,3,4,5,6,7,8,9)";
        // $where[] = "t.custbody_soa_type IN (1,2,3,4)";
        // $where[] = "tl.item IN (100,101,102,103)";
        $where[] = "t.status IN ('A','B','D','E')";


        if (!empty($tranid)) {
            $tranid = str_replace("'", "''", $tranid);

            $where[] = "(
                t.tranid LIKE '%{$tranid}%'
                OR t.transactionNumber LIKE '%{$tranid}%'
            )";
        }

        if (!empty($from)) {
            $where[] = "t.trandate >= TO_DATE('{$from}','YYYY-MM-DD')";
        }

        if (!empty($to)) {
            $where[] = "t.trandate <= TO_DATE('{$to}','YYYY-MM-DD')";
        }
        $sql = "
            SELECT DISTINCT
                t.id,
                t.tranid,
                t.transactionnumber,
                t.trandate,
                BUILTIN.DF(t.entity) AS customer,
                BUILTIN.DF(t.subsidiary) AS subsidiary,
                BUILTIN.DF(t.status) AS status,
                BUILTIN.DF(t.type) AS type,
                t.memo
            FROM transaction t
            INNER JOIN transactionline tl
                ON tl.transaction = t.id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY t.trandate DESC
        ";
        
        // $sql = "
        //     SELECT DISTINCT
        //         t.id,
        //         t.tranid,
        //         t.trandate,
        //         t.transactionNumber
        //     FROM transaction t
        //     JOIN transactionLine tl
        //         ON tl.transaction = t.id
        //     WHERE " . implode(" AND ", $where) . "
        //     ORDER BY t.trandate DESC
        // ";
        // dd($sql);

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

    public function getServiceInvoiceRecord($id)
    {
        $path = "/services/rest/record/v1/invoice/$id";
        $url = $this->client->getConfig('base_uri') . $path;

        $response = $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getCustomerRecord($customerId)
    {
        $path = "/services/rest/record/v1/customer/{$customerId}";
        $url = $this->client->getConfig('base_uri') . $path;

        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getServiceInvoiceItems($invoiceId)
    {
        $path = "/services/rest/record/v1/invoice/{$invoiceId}/item";
        $url = $this->client->getConfig('base_uri') . $path;

        $response = $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
    public function getServiceInvoiceItem($invoiceId, $lineId)
    {
        $path = "/services/rest/record/v1/invoice/{$invoiceId}/item/{$lineId}";
        $url = $this->client->getConfig('base_uri') . $path;

        $response = $this->client->get($path, [
            'headers' => [
                'Authorization' => $this->buildOAuthHeader('GET', $url),
                'Accept' => 'application/json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

}