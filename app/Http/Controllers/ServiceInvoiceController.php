<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NetSuiteService;
use PDF;
use Dompdf\Dompdf;

class ServiceInvoiceController extends Controller
{
    protected $netsuite;

    public function __construct(NetSuiteService $netsuite)
    {
        $this->netsuite = $netsuite;
    }

    // public function index(Request $request)
    // {
    //     $results = [];

    //     if (
    //         $request->filled('tranid') ||
    //         $request->filled('from') ||
    //         $request->filled('to')
    //     ) {
    //         $data = $this->netsuite->searchServiceInvoice(
    //             $request->tranid,
    //             $request->from,
    //             $request->to
    //         );

    //         if (!empty($data['items'])) {
    //             foreach ($data['items'] as $item) {
    //                 $results[] = $this->netsuite->getServiceInvoiceRecord($item['id']);
    //             }
    //         }
    //     }
    //     return view('serviceinvoice.index', [
    //         'results' => $results,
    //     ]);
    // }
    public function index(Request $request)
    {
        $results = [];

        if (
            $request->filled('tranid') ||
            $request->filled('from') ||
            $request->filled('to')
        ) {

            $data = $this->netsuite->searchServiceInvoiceIndex(
                $request->tranid,
                $request->from,
                $request->to
            );

            $results = $data['items'] ?? [];
        }

        // dd($results);
        return view('serviceinvoice.index', compact('results'));
    }

    function print_service_invoice(Request $request, $id)
    {
        $results = [];

        $results = $this->netsuite->getServiceInvoiceRecord($id);
        $itemList = $this->netsuite->getServiceInvoiceItems($id);
        $items = [];
        $customer = null;

        if (!empty($results['entity']['id'])) {
            $customer = $this->netsuite->getCustomerRecord($results['entity']['id']);
        }
        foreach ($itemList['items'] as $index => $item) {
            // $items[] = $this->netsuite->getServiceInvoiceItem($id, $index + 1);
            $href = $item['links'][0]['href'];
            $lineId = basename(parse_url($href, PHP_URL_PATH));

            $items[] = $this->netsuite->getServiceInvoiceItem($id, $lineId);
        }
        // dd($customer);
        // dd($results);
        // dd($items);

        $refName = $customer['subsidiary']['refName'] ?? '';

        if ($refName === 'W Global Realty Inc') {
            $view = 'serviceinvoice.serviceinvoice_wgri';
        } elseif ($refName === 'W Offices Inc') {
            $view = 'serviceinvoice.serviceinvoice_woi';
        } elseif (in_array($refName, ['Ticino Holdings Inc', 'W Offices Inc'])) {
            $view = 'serviceinvoice.serviceinvoice';
        } else {
            $view = 'serviceinvoice.general_service_invoice';
        }
        $pdf = PDF::loadView($view, [
            'details' => $results,
            'customer' => $customer,
            'items' => $items,
        ])->setPaper('A4', 'portrait');
    
        return $pdf->stream('service_invoice.pdf');

    }

}
