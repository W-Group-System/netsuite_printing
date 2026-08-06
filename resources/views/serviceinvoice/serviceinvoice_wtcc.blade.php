<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
@page {
    margin: 0;
}

body{
    margin:0;
    padding:0;
    font-family: Arial, Helvetica, sans-serif;
    font-size:12px;
}

.page{
    margin-top: 7px;
    position:relative;
    width:210mm;
    height:277mm;
}

.abs{
    margin-top: 90px;
}

.abs{
    position:absolute;
}

.right{
    text-align:right;
}

.center{
    text-align:center;
}

.bold{
    font-weight:bold;
}

.desc{
    font-size:11px;
    line-height:15px;
    word-wrap:break-word;
}
</style>

</head>
<body>

<div class="page">
    <div class="container">
        <!-- CUSTOMER NAME -->
        <div class="abs" style="top:120px;left:79px;width:300px;">
            {{ $customer['altName'] ?? '' }}
        </div>

        <!-- ATTENTION -->
        <div class="abs center" style="top:120px;left:420px;width:150px;">
            {{-- {{ $details['attention'] ?? '' }} --}} 
        </div>

        <!-- ISSUE DATE -->
        <div class="abs left" style="top:120px;left:685px;width:95px;">
                {{ $details['tranDate'] ?? '' }}
        </div>

        <!-- TIN -->
        <div class="abs" style="top:146px;left:79px;width:300px;">
            {{ $customer['vatRegNumber'] ?? '' }} 
        </div>

        <!-- DESIGNATION -->
        <div class="abs center" style="top:148px;left:420px;width:150px;">
            {{-- {{ $details['designation'] ?? '' }} --}} 
        </div>

        <!-- DUE DATE -->
        <div class="abs left" style="top:146px;left:685px;width:95px;">
            {{ $details['dueDate'] ?? '' }} 
        </div>

        <!-- ADDRESS -->
        <div class="abs" style="top:172px;left:79px;width:520px;line-height:18px">
            {{ $customer['defaultAddress'] ?? '' }}
        </div>



        {{-- ITEMS --}}
        <div class="items-container">
            @php
                $top = 260;
                $vatInclusive = 0;
                $lessAddVat = 0;
                $netOfVat = 0;
                $lessWithholdingTax = 0;
                $totalAmountDue = 0;
                $hasVatPH = false;
                $hasZeroRate = false;
                $hasVatExempt = false;

                
            @endphp
            @foreach($items ?? [] as $item)
                @php
                    $vatInclusive += $item['grossAmt'];
                    $lessAddVat += $item['tax1Amt'];
                    $netOfVat += $item['rate'];
                    $lessWithholdingTax += (float) ($item['custcol_4601_witaxamount'] ?? 0);
                    $totalAmountDue = $vatInclusive - $lessWithholdingTax;

                    $description = $item['description'];
                    $currentTop = strlen($description) > 85 ? $top - 4 : $top;

                    $wrapped = wordwrap(strip_tags($description), 85, "\n");
                    $lines = substr_count($wrapped, "\n") + 0;

                    $rowHeight = max(25, $lines * 20);
                    $lineHeight = strlen($description) > 94 ? '10px' : 'normal';

                    if (($item['taxCode']['refName'] ?? '') === 'VAT_PH:S-PH') {
                        $hasVatPH = true;
                    }
                    if (($item['taxCode']['refName'] ?? '') === 'VAT_PH:Z-PH') {
                        $hasZeroRate = true;
                    }
                    if (($item['taxCode']['refName'] ?? '') === 'VAT_PH:EX-PH') {
                        $hasVatExempt = true;
                    }
                @endphp

                @break($loop->index >= 8)
               <div class="abs desc"
                    style="top:{{$currentTop}}px;left:80px;width:450px;
                            line-height:{{ strlen($description) > 70 ? '12px' : 'normal' }};">
                    {{ $item['description'] }}
                </div>

                <div class="abs center"
                    style="top:{{$currentTop}}px;left:531px;width:103px;">
                    {{ number_format($item['rate'],2) }}
                </div>

                <div class="abs center"
                    style="top:{{$currentTop}}px;left:636px;width:110px;">
                    {{ number_format($item['grossAmt'],2) }}
                </div>

                @php
                    $top += $rowHeight;
                @endphp

            @endforeach
        </div>
        
        <!-- VATABLE SALES -->
        <div class="abs center" style="top:487px;left:100px;width:230px;">
            @if ($hasVatPH == true)
                {{ !empty($netOfVat) ? number_format($netOfVat, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- VAT -->
        <div class="abs center" style="top:513px;left:100px;width:230px;">
            @if ($hasVatPH == true)
                {{ !empty($lessAddVat) ? number_format($lessAddVat, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- ZERO RATED -->
        <div class="abs center" style="top:537px;left:100px;width:230px;">
            @if ($hasZeroRate == true)
                {{ !empty($vatInclusive) ? number_format($vatInclusive, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- VAT EXEMPT -->
        <div class="abs center" style="top:560px;left:100px;width:230px;">
            @if ($hasVatExempt == true)
                {{ !empty($totalAmountDue) ? number_format($totalAmountDue, 2) : '-' }}
            @else
                -
            @endif
            {{-- {{ !empty($vatInclusive) ? number_format($vatInclusive, 2) : '-' }} --}}
        </div>



        <!-- TOTAL SALES -->
        <div class="abs center" style="top:464px;left:640px;width:105px;">
            {{ !empty($vatInclusive) ? number_format($vatInclusive, 2) : '' }}
        </div>

        <!-- LESS VAT -->
        <div class="abs center" style="top:485px;left:640px;width:105px;">
            {{ !empty($lessAddVat) ? number_format($lessAddVat, 2) : '-' }}
        </div>

        <!-- NET OF VAT -->
        <div class="abs center" style="top:510px;left:640px;width:105px;">
            @if ($hasVatPH == true || $hasVatExempt == true)
                {{ !empty($netOfVat) ? number_format($netOfVat, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- ADD VAT -->
        <div class="abs center" style="top:537px;left:640px;width:105px;">
            @if ($hasVatPH == true)
                {{ !empty($lessAddVat) ? number_format($lessAddVat, 2) : '' }}
            @else
                -
            @endif
        </div>

        <!-- WITHHOLDING TAX -->
        <div class="abs center" style="top:560px;left:640px;width:105px;">
            {{ !empty($lessWithholdingTax) ? number_format($lessWithholdingTax, 2) : '' }}
        </div>

        <!-- TOTAL DUE -->
        <div class="abs center bold" style="top:587px;left:640px;width:105px;font-size:14px;">
            {{ !empty($totalAmountDue) ? number_format($totalAmountDue, 2) : '' }}
        </div>



        {{-- EWT SUMMARY --}}
        @php
            $ewtTop = 648;
        @endphp

        @php
            $ewt5 = collect($items ?? [])
                ->where('custcol_4601_witaxrate', 5)
                ->sum(function ($item) {
                    return $item['custcol_4601_witaxamount'] ?? 0;
                });

            $ewt2 = collect($items ?? [])
                ->where('custcol_4601_witaxrate', 2)
                ->sum(function ($item) {
                    return $item['custcol_4601_witaxamount'] ?? 0;
                });
        @endphp

        <!-- 5% -->
        <div class="abs center" style="top:{{$ewtTop}}px;left:520px;width:130px;">
            {{ $ewt5 != 0 ? number_format(abs($ewt5), 2) : '-' }}
        </div>

        @php $ewtTop += 26; @endphp

        <!-- 2% -->
        <div class="abs center" style="top:{{$ewtTop}}px;left:520px;width:130px;">
            {{ $ewt2 != 0 ? number_format(abs($ewt2), 2) : '-' }}
        </div>

         <div class="abs left" style="top:668px;left:100px;width:200px;">
            @if ($details['subsidiary']['refName'] === "W Landmark Inc.")
                {{ $details['subsidiary']['refName'] ?? '' }}

            @else
            @endif
        </div>
        <div class="abs center" style="top:700px;left:100px;width:130px;">
            {{ $details['dueDate'] ?? '' }} 
        </div>


        <!-- PREPARED BY -->
        <div class="abs left" style="top:828px;left:60px;width:250px;">
            {{-- {{ $details['preparedBy'] ?? '' }} --}}
            @if (( $details['subsidiary']['refName'] == "W Offices Inc" ) || ( $details['subsidiary']['refName'] == "Ticino Holdings Inc" ) || ( $details['subsidiary']['refName'] == "Hyopan Land Philippines Inc" ) || ( $details['subsidiary']['refName'] == "W Global Realty Inc" ))
                JSC / JAC
            @elseif (( $details['subsidiary']['refName'] == "W Fifth Avenue, Inc." ) || ( $details['subsidiary']['refName'] == "W Landmark Inc." ) || ( $details['subsidiary']['refName'] == "W Tower Condominium Corporation" ))    
                MJR / ADG
            @endif
        </div>

        <div class="abs left" style="top:838px;left:300px;width:250px;">
            {{ $details['tranId'] ?? '' }}
        </div>

    </div>
</div>
</body>
</html>