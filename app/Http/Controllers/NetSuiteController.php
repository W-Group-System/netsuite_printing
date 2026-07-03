<?php

namespace App\Http\Controllers;

use App\ApVoucher;
use App\Name;
use Illuminate\Http\Request;
use App\Services\NetSuiteService;
use PDF;
use Dompdf\Dompdf;

class NetSuiteController extends Controller
{
    protected $netsuite;

    public function __construct(NetSuiteService $netsuite)
    {
        $this->netsuite = $netsuite;
    }

    // public function searchVendorBill(Request $request)
    // {
    //     $tranid = $request->input('tranid');

    //     $data = app(\App\Services\NetSuiteService::class)
    //         ->getVendorBill($request->tranid);

    //     return view('netsuite.result', [
    //         'result' => $data['items'][0] ?? null
    //     ]);
    // }

    
    public function searchVendorBill(Request $request)
    {
        $checkers = Name::where(function ($query) {
            $query->where('role', 'Checker')
                ->orWhereNull('role');
        })->get();
        $approvers = Name::where(function ($query) {
            $query->where('role', 'Approver')
                ->orWhereNull('role');
        })->get();
        $results = [];
        if ($request->filled('tranid')) {
            $tranid = $request->tranid;

            $data = $this->netsuite->searchVendorBill($tranid);

            $results = [];

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $results[] = $this->netsuite->getVendorBillRecord($item['id']);
                }
            }
        }
        
        $apVoucherData = ApVoucher::get()->keyBy('bill_id');
        return view('netsuite.result', [
            'results' => $results,
            'apVoucherData' => $apVoucherData,
            'checkers' => $checkers,
            'approvers' => $approvers,
        ]);
    }

    function print_voucher(Request $request, $id)
    {

        $vendorBill = $this->netsuite->getVendorBillRecord($id);
        $vendorBillItems = $this->netsuite->getVendorBillItems($id);
        $gl = $this->netsuite->getVendorBillGLImpact($id);
        $apVoucher = ApVoucher::where('bill_id', $id)->first();

        $glCredit = [];
        $glDebit = [];
        $grnGlCredit = [];
        $grnGlDebit = [];

        $poIds = [];

        foreach ($vendorBillItems['items'] ?? [] as $item) {

            if (!empty($item['links'][0]['href'])) {

                $href = $item['links'][0]['href'];

                $lineData = $this->netsuite->getRawUrl($href);

                if (!empty($lineData['orderDoc']['id'])) {
                    $poIds[] = $lineData['orderDoc']['id'];
                }
            }
        }

        $poIds = array_values(array_unique($poIds));

        $purchaseOrders = [];
        $itemReceipts = [];
        $grnGlLines = [];
        $allGrnGl = [];

        foreach ($poIds as $poId) {
            $purchaseOrders[] = $this->netsuite->getPurchaseOrder($poId);

            $receipts = $this->netsuite->getItemReceiptsByPO($poId);

            foreach ($receipts['items'] ?? [] as $receipt) {
                $itemReceipts[] = $receipt;
                $grnGl = $this->netsuite->getGRNGLImpact($receipt['id']);

                $allGrnGl[] = $grnGl;
            }
        }
        
        foreach ($allGrnGl as $grn) {
            foreach ($grn['items'] ?? [] as $line) {
                $grnGlLines[] = $line;
            }
        }

        foreach ($grnGlLines as $line) {

            if (!empty($line['credit'])) {
                $grnGlCredit[] = $line;
            }

            if (!empty($line['debit'])) {
                $grnGlDebit[] = $line;
            }
        }
        // dd($grnGlDebit);
        

        foreach ($gl['items'] ?? [] as $line) {

            if (!empty($line['credit'])) {
                $glCredit[] = $line;
            }

            if (!empty($line['debit'])) {
                $glDebit[] = $line;
            }
        }
        $withholdingTax = $this->netsuite->getVendorBillWithholdingTax($id);
        $withholdingTaxAmount = $withholdingTax['items'][0]['withholdingtax'] ?? "";
        $creator = $this->netsuite->getVendorBillCreator($id);

        $creatorName = '';

        if (!empty($creator['items'][0])) {
            $creatorName =
                ($creator['items'][0]['firstname'] ?? '') . ' ' .
                ($creator['items'][0]['lastname'] ?? '');
        }
        $results = [
            'vendorBill' => $vendorBill,
            'glCredit'   => $glCredit,
            'glDebit'    => $glDebit,
            'purchaseOrders' => $purchaseOrders,
            'grnGlCredit' => $grnGlCredit,
            'grnGlDebit'  => $grnGlDebit,
            'itemReceipts' => $itemReceipts,
            'withholdingTaxAmount' => $withholdingTaxAmount,
            'creatorName' => $creatorName,
            'apVoucher' => $apVoucher,
        ];
        // dd($vendorBill);
        
        $pdf = PDF::loadView('netsuite.apvoucher', [
            'details' => $results,
        ])->setPaper('A4', 'portrait');
    
        return $pdf->stream('voucher.pdf');

    }

    function new_ap(Request $request, $id){
        $save_as_new = new ApVoucher;
        $save_as_new->bill_id = $id;
        $save_as_new->invoice_billing_date = $request->invoice_billing_date;
        $save_as_new->bmo_received_date = $request->bmo_received_date; 
        $save_as_new->checked_by = $request->checked_by; 
        $save_as_new->approved_by = $request->approved_by; 
        $save_as_new->rush = $request->rush;
        $save_as_new->save();
        return redirect()->back()->with('success', 'AP Voucher saved successfully.');
    }

    public function update_ap(Request $request, $id)
    {
        $apVoucher = ApVoucher::findOrFail($id);

        $apVoucher->invoice_billing_date = $request->invoice_billing_date;
        $apVoucher->bmo_received_date = $request->bmo_received_date;
        $apVoucher->checked_by = $request->checked_by;
        $apVoucher->approved_by = $request->approved_by;
        $apVoucher->rush = $request->has('rush');

        $apVoucher->save();

        return redirect()->back()->with('success', 'AP Voucher updated successfully.');
    }
}