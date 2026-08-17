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
    margin-top: 28px;
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
        <div class="abs" style="top:116px;left:81px;width:300px;">
            {{ $customer['altName'] ?? '' }}
        </div>

        <!-- ATTENTION -->
        <div class="abs center" style="top:114px;left:420px;width:150px;">
            {{-- {{ $details['attention'] ?? '' }} --}} 
        </div>

        <!-- ISSUE DATE -->
        <div class="abs left" style="top:115px;left:667px;width:95px;">
                {{ $details['tranDate'] ?? '' }}
        </div>

        <!-- TIN -->
        <div class="abs" style="top:140px;left:81px;width:300px;">
            {{ $customer['vatRegNumber'] ?? '' }} 
        </div>

        <!-- DESIGNATION -->
        <div class="abs center" style="top:140px;left:420px;width:150px;">
            {{-- {{ $details['designation'] ?? '' }} --}} 
        </div>

        <!-- DUE DATE -->
        <div class="abs left" style="top:139px;left:667px;width:95px;">
            {{ $details['dueDate'] ?? '' }} 
        </div>

        <!-- ADDRESS -->
        <div class="abs" style="top:163px;left:81px;width:520px;line-height:18px">
            {{ $customer['defaultAddress'] ?? '' }}
        </div>



        {{-- ITEMS --}}
        <div class="items-container">
            @php
                // $top = 245;
                $top = 242;
                $vatInclusive = 0;
                $lessAddVat = 0;
                $netOfVat = 0;
                $lessWithholdingTax = 0;
                $totalAmountDue = 0;
                $hasVatPH = false;
                $hasZeroRate = false;
                $hasVatExempt = false;

                $interestPenaltyItems = collect($items ?? [])->filter(function ($item) {
                    $description = trim(strip_tags($item['description'] ?? ''));

                    return stripos($description, 'interest and penalties') === 0;
                });

                $interestPenaltyRate = $interestPenaltyItems->sum(function ($item) {
                    return (float) ($item['rate'] ?? 0);
                });

                $interestPenaltyAmount = $interestPenaltyItems->sum(function ($item) {
                    return (float) ($item['grossAmt'] ?? 0);
                });

                $interestPenaltyMemo = $details['memo'] ?? 'Interest and Penalties';

                $interestPenaltyShown = false;

                
            @endphp
            @foreach($items ?? [] as $item)
                @php
                    $description = strip_tags($item['description'] ?? '');

                    $isInterestPenalty = stripos(
                        $description,
                        'interest and penalties'
                    ) === 0;

                    if ($isInterestPenalty) {
                        if ($interestPenaltyShown) {
                            continue;
                        }

                        $interestPenaltyShown = true;

                        $description = $interestPenaltyMemo;
                        $rate = $interestPenaltyRate;
                        $grossAmt = $interestPenaltyAmount;
                    } else {
                        $rate = (float) ($item['rate'] ?? 0);
                        $grossAmt = (float) ($item['grossAmt'] ?? 0);
                    }

                    $vatInclusive += $grossAmt;
                    $lessAddVat += $item['tax1Amt'];
                    $netOfVat += $rate;
                    $lessWithholdingTax += (float) ($item['custcol_4601_witaxamount'] ?? 0);
                    $totalAmountDue = $vatInclusive - $lessWithholdingTax;

                    // $description = $item['description'];
                    // $currentTop = strlen($description) > 85 ? $top - 4 : $top;

                    // $wrapped = wordwrap(strip_tags($description), 85, "\n");
                    // $lines = substr_count($wrapped, "\n") + 0;

                    // $rowHeight = max(25, $lines * 20);
                    // $lineHeight = strlen($description) > 94 ? '10px' : 'normal';

                    $wrapped = wordwrap($description, 90, "\n");
                    $lines = max(1, substr_count($wrapped, "\n") + 1);

                    $boxHeight = 23;
                    $rowHeight = ceil($lines / 2) * $boxHeight;

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

                {{-- @break($loop->index >= 8) --}}
               {{-- <div class="abs desc"
                    style="top:{{$top}}px;left:62px;width:461px;
                            line-height:{{ strlen($description) > 70 ? '12px' : 'normal' }}; ">
                    {{ $item['description'] }}
                </div>

                <div class="abs center"
                    style="top:{{$currentTop}}px;left:519px;width:101px;">
                    {{ number_format($item['rate'],2) }}
                </div>

                <div class="abs center"
                    style="top:{{$currentTop}}px;left:624px;width:114px;">
                    {{ number_format($item['grossAmt'],2) }}
                </div> --}}
                <div class="abs desc"
                    style="
                        top:{{$top}}px;
                        left:62px;
                        width:461px;
                        line-height:12px;
                    ">
                    {{ $description }}
                </div>

                <div class="abs center"
                    style="
                        top:{{$top}}px;
                        left:519px;
                        width:101px;
                    ">
                    {{ number_format($rate, 2) }}
                </div>

                <div class="abs center"
                    style="
                        top:{{$top}}px;
                        left:624px;
                        width:114px;
                    ">
                    {{ number_format($grossAmt, 2) }}
                </div>

                @php
                    $top += $rowHeight;
                @endphp

            @endforeach
        </div>
        
        <!-- VATABLE SALES -->
        <div class="abs center" style="top:462px;left:104px;width:230px;">
            @if ($hasVatPH == true)
                {{ !empty($netOfVat) ? number_format($netOfVat, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- VAT -->
        <div class="abs center" style="top:487px;left:104px;width:230px;">
            @if ($hasVatPH == true)
                {{ !empty($lessAddVat) ? number_format($lessAddVat, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- ZERO RATED -->
        <div class="abs center" style="top:509px;left:104px;width:230px;">
            @if ($hasZeroRate == true)
                {{ !empty($vatInclusive) ? number_format($vatInclusive, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- VAT EXEMPT -->
        <div class="abs center" style="top:532px;left:104px;width:230px;">
            @if ($hasVatExempt == true)
                {{ !empty($totalAmountDue) ? number_format($totalAmountDue, 2) : '-' }}
            @else
                -
            @endif
            {{-- {{ !empty($vatInclusive) ? number_format($vatInclusive, 2) : '-' }} --}}
        </div>



        <!-- TOTAL SALES -->
        <div class="abs center" style="top:435px;left:626px;width:105px;">
            {{ !empty($vatInclusive) ? number_format($vatInclusive, 2) : '' }}
        </div>

        <!-- LESS VAT -->
        <div class="abs center" style="top:460px;left:626px;width:105px;">
            {{ !empty($lessAddVat) ? number_format($lessAddVat, 2) : '-' }}
        </div>

        <!-- NET OF VAT -->
        <div class="abs center" style="top:485px;left:626px;width:105px;">
            @if ($hasVatPH == true || $hasVatExempt == true)
                {{ !empty($netOfVat) ? number_format($netOfVat, 2) : '-' }}
            @else
                -
            @endif
        </div>

        <!-- ADD VAT -->
        <div class="abs center" style="top:510px;left:626px;width:105px;">
            @if ($hasVatPH == true)
                {{ !empty($lessAddVat) ? number_format($lessAddVat, 2) : '' }}
            @else
                -
            @endif
        </div>

        <!-- WITHHOLDING TAX -->
        <div class="abs center" style="top:534px;left:626px;width:105px;">
            {{ !empty($lessWithholdingTax) ? number_format($lessWithholdingTax, 2) : '' }}
        </div>

        <!-- TOTAL DUE -->
        <div class="abs center bold" style="top:556px;left:626px;width:105px;font-size:14px;">
            {{ !empty($totalAmountDue) ? number_format($totalAmountDue, 2) : '' }}
        </div>



        {{-- EWT SUMMARY --}}
        @php
            $ewtTop = 617;
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

         <div class="abs left" style="top:638px;left:100px;width:200px;">
            {{-- {{ $details['subsidiary']['refName'] ?? '' }} --}}
        </div>
        <div class="abs center" style="top:663px;left:100px;width:130px;">
            {{ $details['dueDate'] ?? '' }} 
        </div>


        <!-- PREPARED BY -->
        <div class="abs left" style="top:789px;left:100px;width:250px;">
            {{-- {{ $details['preparedBy'] ?? '' }} --}}
            @if (( $details['subsidiary']['refName'] == "W Offices Inc" ) || ( $details['subsidiary']['refName'] == "Ticino Holdings Inc" ) || ( $details['subsidiary']['refName'] == "Hyopan Land Philippines Inc" ) || ( $details['subsidiary']['refName'] == "W Global Realty Inc" ))
                JSC / JAC
            @elseif (( $details['subsidiary']['refName'] == "W Fifth Avenue, Inc." ) || ( $details['subsidiary']['refName'] == "W Landmark Inc." ) || ( $details['subsidiary']['refName'] == "W Tower Condominium Corporation" ))    
                MJR / ADG
            @endif
        </div>

        <div class="abs left" style="top:818px;left:300px;width:250px;">
            {{ $details['tranId'] ?? '' }}
        </div>

    </div>
</div>
</body>
</html>