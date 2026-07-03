<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accounts Payable Voucher</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            padding: 18px 20px 12px;
        }

        .hdr { text-align: center; margin-bottom: 8px; }
        .hdr-img img { 
            margin-top: 150px;
            width: 20%;
            height: 4%;
        }
        .hdr-sub   { font-size: 15px; letter-spacing: 2px; margin-top: 50px; font-weight: bold}

        .lbl  { font-size: 9px;  display: block; line-height: 1.3; }
        .ref-row .val  { font-size: 11px; min-height: 14px; display: block; margin-top: 1px; text-align:center; font-weight: bold;}
        
        .val  { font-size: 11px; min-height: 14px; display: block; margin-top: 1px; text-align:center; font-weight: bold}

        /* ── Main top grid ── */
        table.main,
        table.bottom {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;

        }
        table.grn-entry, table.bill-entry {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;

        }
        td, th {
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        table.main td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
        }
        table.main .body-row td {
            height: 120px;
            border-top: none;
            border-bottom: none;
        }
        .amount-value {
            font-size: 18px;
            text-align: center;
            margin-top: 30px; 
        }

        /* table.grn-entry td, table.grn-entry th {
            padding: 2px 5px;
            font-size: 8px;
            vertical-align: top;
        }
        table.grn-entry th {
            font-weight: bold;
            text-align: left;
        }
        table.grn-entry .body-row td {
            height: 90px;
            border-top: none;
            border-bottom: none;
        }
        table.grn-entry .total-row td {
            font-weight: bold;
            text-align: center;
        }
        table.grn-entry .total-row td.blank {
            text-align: center;
        } */

        table.grn-entry th {
            font-weight: bold;
            text-align: left;
        }
        table.grn-entry .body-row td {
            height: 90px;
            border-top: none;
            border-bottom: none;
        }
        table.grn-entry .total-row td {
            font-weight: bold;
            text-align: center;
        }
        table.grn-entry .total-row td.blank {
            text-align: center;
        }

        table.grn-entry td,
        table.grn-entry th,
        table.bill-entry td,
        table.bill-entry th {
            padding: 2px 5px;
            font-size: 10px;
            vertical-align: top;
        }
        table.bill-entry th {
            font-weight: bold;
            text-align: left;
        }
        table.bill-entry .body-row td {
            height: 90px;
            border-top: none;
            border-bottom: none;
        }
        table.bill-entry .total-row td {
            font-weight: bold;
            text-align: center;
        }
        table.bill-entry .total-row td.blank {
            text-align: center;
        }

        table.bottom td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
            font-size: 9px;
        }

        table.bottom .lbl {
            padding-bottom:10px
        }

        .sig-line {
            border-top: 1px solid #000;
            font-size: 8px;
            color: #070606;
            text-align: center;
            padding-top: 1px;
            margin-top: 5px;
            font-weight: bold;
        }

        .doc-ft {
            /* padding-left: 15;
            padding-right: 15; */
            /* position: fixed; */
            /* bottom: 90; */
            width: 100%;
            font-size: 8px;
        }
    </style>
</head>
<body>

    <div class="hdr">
        <div class="hdr-img">
            {{-- <img src="{{ asset('images/WBGC.png') }}" alt=""> --}}
        </div>
        <div class="hdr-sub">ACCOUNTS PAYABLE VOUCHER</div>
    </div>

    @php
        $totalDebit = 0;
        $totalCredit = 0;
        $totalGrnDebit = 0;
        $totalGrnCredit = 0;
    @endphp

    <table class="main">
        {{-- <colgroup>
            <col style="width:12%"> 
            <col style="width:11%"> 
            <col style="width:11%"> 
            <col style="width:14%"> 
            <col style="width:12%"> 
            <col style="width:13%"> 
            <col style="width:12%"> 
            <col style="width:15%"> 
        </colgroup> --}}

        <tr>
            <td colspan="4" style="height:40px">
                <span class="lbl">COMPANY:</span>
                <span class="val">{{ $details['vendorBill']['subsidiary']['refName'] ?? '' }}</span>
            </td>
            <td colspan="2">
                <span class="lbl">APV No.:</span>
                <span class="val">{{ $voucher->apv_no ?? '' }}</span>
            </td>
        </tr>

        <tr class="ref-row" style="min-height:36px">
            <td colspan="1" style="height:55px">
                <span class="lbl">REFERENCE NUMBER:</span>
                <span class="val">{{ $details['vendorBill']['tranId'] ?? '' }}</span>
            </td>
            <td colspan="1">
                <span class="lbl">PO NUMBER:</span>
                <span class="val">{{ $details['purchaseOrders'][0]['transactionNumber'] ?? '' }}</span>
            </td>
            <td colspan="1">
                <span class="lbl">GRN NUMBER</span>
                <span class="val">{{ implode(' / ', array_unique(array_filter(array_column($details['itemReceipts'] ?? [], 'transactionnumber')))) }}</span>
            </td>
            <td colspan="1">
                <span class="lbl">TRANSACTION NUMBER:</span>
                <span class="val">{{ $details['vendorBill']['transactionNumber'] ?? '' }}</span>
            </td>
            <td colspan="1">
                <span class="lbl">DEFECTIVE:</span>

                <span style="font-family: DejaVu Sans, sans-serif; display: block; margin-top: 1px; text-align:center;">
                    {{ !empty($details['vendorBill']['custbody37']) ? '■' : '☐' }} YES
                </span>

                <span style="font-family: DejaVu Sans, sans-serif; display: block; margin-top: 1px; text-align:center;">
                    {{ empty($details['vendorBill']['custbody37']) ? '■' : '☐' }} NO
                </span>
            </td>
            <td colspan="1">
                <span class="lbl">DOCUMENTS NEEDED:</span>
                <span class="val">{{ $voucher->documents_needed ?? '' }}</span>
            </td>
            
        </tr>

        <tr>
            <td colspan="4" style="height:40px">
                <span class="lbl">VENDOR:</span>
                <span class="val">{{ $details['vendorBill']['entity']['refName'] ?? '' }}</span>
            </td>
            <td>
                <span class="lbl">EWT %:</span>
                <span class="val">{{ $voucher->ewt_percent ?? '' }}</span>
            </td>
            <td >
                <span class="lbl">EWT AMOUNT:</span>
                <span class="val">{{ number_format($details['withholdingTaxAmount'],2) ?? '' }}</span>
            </td>
        </tr>

        <tr class="body-row">
            <td colspan="4">
                <span class="lbl">MEMO:</span>
                <span class="val">{{ $details['vendorBill']['memo'] ?? '' }}</span>
            </td>
            <td colspan="2">
                <div class="lbl">AMOUNT:</div>
                <div class="val amount-value">
                    {{ number_format($details['vendorBill']['userTotal'], 2) }}
                </div>
            </td>
        </tr>

    </table>
    <div style="height:210px; overflow:hidden;border:1px solid black">
        <table class="grn-entry">
            <tr>
                <td style="width:64%;font-weight:bold;font-size:8px"  colspan="3">GRN ENTRY</td>
            </tr>
            <tr>
                <th style="width:72%;padding-left:14px">ACCOUNT CODE</th>
                <th style="width:14%;text-align:center">DEBIT</th>
                <th style="width:14%;text-align:center">CREDIT</th>
            </tr>
            @php
                $groupedDebit = collect($details['grnGlDebit'] ?? [])
                    ->groupBy(function ($item, $key) {
                        return preg_match('/^[45]/', $item['acctnumber'])
                            ? $item['acctnumber'] . '_' . $key   // Don't consolidate
                            : $item['acctnumber'];               // Consolidate
                    })
                    ->map(function ($items) {
                        return [
                            'acctnumber' => $items->first()['acctnumber'],
                            'fullname'   => $items->first()['fullname'],
                            'debit'      => $items->sum('debit'),
                        ];
                    })
                    ->values();

                $groupedCredit = collect($details['grnGlCredit'] ?? [])
                    ->groupBy(function ($item, $key) {
                        return preg_match('/^[45]/', $item['acctnumber'])
                            ? $item['acctnumber'] . '_' . $key
                            : $item['acctnumber'];
                    })
                    ->map(function ($items) {
                        return [
                            'acctnumber' => $items->first()['acctnumber'],
                            'fullname'   => $items->first()['fullname'],
                            'credit'     => $items->sum('credit'),
                        ];
                    })
                    ->values();

                $groupedGlDebit = collect($details['glDebit'] ?? [])
                    ->groupBy(function ($item, $key) {
                        return preg_match('/^[45]/', $item['acctnumber'])
                            ? $item['acctnumber'] . '_' . $key
                            : $item['acctnumber'];
                    })
                    ->map(function ($items) {
                        return [
                            'acctnumber' => $items->first()['acctnumber'],
                            'fullname'   => $items->first()['fullname'],
                            'debit'      => $items->sum('debit'),
                        ];
                    })
                    ->values();

                $groupedGlCredit = collect($details['glCredit'] ?? [])
                    ->groupBy(function ($item, $key) {
                        return preg_match('/^[45]/', $item['acctnumber'])
                            ? $item['acctnumber'] . '_' . $key
                            : $item['acctnumber'];
                    })
                    ->map(function ($items) {
                        return [
                            'acctnumber' => $items->first()['acctnumber'],
                            'fullname'   => $items->first()['fullname'],
                            'credit'     => $items->sum('credit'),
                        ];
                    })
                    ->values();
            @endphp
            @if($groupedDebit->count())
                @foreach($groupedDebit as $grnGld)
                @php
                    $totalGrnDebit +=   $grnGld['debit']
                @endphp
                    <tr>
                        <td style="width:72%">
                            {{ $grnGld['acctnumber'] ?? '' }} {{ $grnGld['fullname'] ?? '' }}
                        </td>
                        <td style="width:14%;text-align:center">
                            {{ number_format($grnGld['debit'],2) ?? '' }}
                        </td>
                        <td style="width:14%;text-align:center"></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td style="width:72%">N/A</td>
                    <td style="width:14%;text-align:center"></td>
                    <td style="width:14%;text-align:center"></td>
                </tr>
            @endif
            @if($groupedCredit->count())
                @foreach($groupedCredit as $grnGlc)
                @php
                    $totalGrnCredit +=   $grnGlc['credit']
                @endphp
                    <tr>
                        <td style="width:72%">
                            {{ $grnGlc['acctnumber'] ?? '' }} {{ $grnGlc['fullname'] ?? '' }}
                        </td>
                        <td style="width:14%;text-align:center"></td>
                        <td style="width:14%;text-align:center">
                            {{ number_format($grnGlc['credit'],2) ?? '' }}
                        </td>
                    </tr>
                @endforeach
            @endif
            <tr class="total-row">
                <td class="blank"></td>
                <td style="text-align:center;">
                    <div style="display:inline-block; border-top:1px solid #000; width:80px;">
                        {{ number_format($totalGrnDebit,2) ?? '0.00' }}
                    </div>
                </td>
                <td style="text-align:center;">
                    <div style="display:inline-block; border-top:1px solid #000; width:80px;">
                        {{ number_format($totalGrnCredit,2) ?? '0.00' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    

    {{-- ── BILL ENTRY ── --}}
    <div style="height:300px; overflow:hidden;border:1px solid black">
        <table class="bill-entry">
            <tr>
                <td style="width:64%;font-weight:bold;font-size:8px" colspan="3">BILL ENTRY</td>
            </tr>
            <tr>
                <th style="width:72%;padding-left:14px">ACCOUNT CODE</th>
                <th style="width:14%;text-align:center">DEBIT</th>
                <th style="width:14%;text-align:center">CREDIT</th>
            </tr>
            @foreach ( $groupedGlDebit as $glDebit)
            
            @php
                $totalDebit +=   $glDebit['debit']
            @endphp
                <tr>
                    <td style="width:72%">{{ $glDebit['acctnumber'] ?? '' }} {{ $glDebit['fullname'] ?? '' }}</td>
                    <td style="width:14%;text-align:center">{{ number_format($glDebit['debit'],2) ?? '' }}</td>
                    <td style="width:14%;text-align:center"></td>
                </tr>
            @endforeach
            @foreach ( $groupedGlCredit as $glCredit)

            @php
                $totalCredit +=   $glCredit['credit']
            @endphp

                <tr>
                    <td style="width:72%">{{ $glCredit['acctnumber'] ?? '' }} {{ $glCredit['fullname'] ?? '' }}</td>
                    <td style="width:14%;text-align:center"></td>
                    <td style="width:14%;text-align:center">{{ number_format($glCredit['credit'],2) ?? '' }}</td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td class="blank"></td>
                <td style="text-align:center;">
                    <div style="display:inline-block; border-top:1px solid #000; width:80px;">
                        {{ number_format($totalDebit,2) ?? '0.00' }}
                    </div>
                </td>
                <td style="text-align:center;">
                    <div style="display:inline-block; border-top:1px solid #000; width:80px;">
                        {{ number_format($totalCredit,2) ?? '0.00' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    

    {{-- ── DATES ROW ── --}}
    <table class="bottom">
        <tr>
            <td style="width:20%; height:40px">
                <span class="lbl">INVOICE/BILLING DATE:</span>
                <span class="val">{{ optional($details['apVoucher'])->invoice_billing_date ? date('m-d-Y', strtotime($details['apVoucher']->invoice_billing_date)) : '' }}</span>
            </td>
            <td style="width:22%">
                <span class="lbl">FRONT OFFICE RECEIVED DATE:</span>
                <span class="val">{{ optional($details['apVoucher'])->bmo_received_date ? date('m-d-Y', strtotime($details['apVoucher']->bmo_received_date)) : '' }}</span>
            </td>
            <td style="width:18%">
                <span class="lbl">APD RECEIVED DATE:</span>
                <span class="val">{{ optional($details['vendorBill']['custbody46']) ? date('m-d-Y', strtotime($details['vendorBill']['custbody46'])) : '' }}</span>
            </td>
            <td style="width:15%">
                <span class="lbl">DUE DATE:</span>
                <span class="val">{{ optional($details['vendorBill']['dueDate']) ? date('m-d-Y', strtotime($details['vendorBill']['dueDate'])) : '' }}</span>
            </td>
            <td>
                <span>RUSH</span>
                <span style="font-family: DejaVu Sans, sans-serif; font-size:13px;  vertical-align:middle;">
                    {{ !empty(optional($details['apVoucher'])->rush) ? '■' : '☐' }}
                </span>
            </td>
        </tr>
        <tr>
            <td style="width:20%;height:54px">
                <span class="lbl">Prepared by:</span>
                <span class="val">{{ $details['creatorName'] }}</span>
                <div class="sig-line">AP ANALYST</div>
            </td>
            <td style="width:20%">
                <span class="lbl">Checked by:</span>
                <span class="val">RC</span>
                <div class="sig-line">AP OFFICER</div>
            </td>
            <td style="width:20%">
                <span class="lbl">Approved by:</span>
                <span class="val">MPG</span>
                <div class="sig-line">AP SUPERVISOR</div>
            </td>
            <td style="width:20%">
                <span class="lbl">Date Transferred to TMD:</span>
                <span class="val">{{ $voucher->date_transferred ?? '' }}</span>
            </td>
            <td style="width:20%">
                <span class="lbl">TMD Remarks/ Check No.:</span>
                <span class="val">{{ $voucher->tmd_remarks ?? '' }}</span>
            </td>
        </tr>
    </table>

    <table class="doc-ft">
        <tr>
            <td>WLI-FR-APO-001<br>Rev. 2 07/02/2025</td>
            <td style="text-align:right">Page 1 of 1</td>
        </tr>
    </table>

</body>
</html>
