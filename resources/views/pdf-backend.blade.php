<!DOCTYPE html>
<?php use App\Helper\ArabicConvertorHelper; ?>
@php
// Check if function is already declared
    if (!function_exists('convertNumberToArabicWords')) {
        function convertNumberToArabicWords($number, $units, $tens, $hundreds) {
            $words = [];

            if ($number < 0) {
                $words[] = 'ناقص';
                $number = abs($number);
            }

            if ($number === 0) {
                $words[] = 'صفر';
            } else {
                // Handle thousands
                if ($number >= 1000) {
                    $thousands = floor($number / 1000);
                    if ($thousands == 1) {
                        $words[] = 'ألف';
                    } elseif ($thousands == 2) {
                        $words[] = 'ألفان';
                    } else {
                        $words[] = $units[$thousands] . ' آلاف';
                    }
                    $number %= 1000;
                }

                // Handle hundreds
                if ($number >= 100) {
                    $words[] = $hundreds[floor($number / 100)];
                    $number %= 100;
                }

                // Handle tens and units for two-digit numbers (20 to 99)
                if ($number >= 20) {
                    $tensPart = floor($number / 10); // Get the tens place
                    $unitsPart = $number % 10; // Get the units place

                    // Reverse the order to show units before tens (e.g., "واحد و عشرو")
                    if ($unitsPart > 0) {
                        $words[] = $units[$unitsPart];
                    }
                    $words[] = $tens[$tensPart];
                    $number = 0; // We've handled the number, set it to zero
                }

                // Handle units and special cases from 1 to 19
                if ($number > 0) {
                    $words[] = $units[$number];
                }
            }

            // Combine all parts into a single string
            return implode('  ', array_filter($words));
        }
    }
@endphp
<html>

<head>
    <title>PDF</title>
    <style>
        /* General body styling */
        body {
            margin: 0;
            direction: rtl;
            color: "#000000";
            padding: 0px;
            /*font-family: 'Almarai', sans-serif;*/
            font-family:"xbriyaz"
        }

        /* Header styling */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: auto;
            background: transparent;
            border-bottom: 0px solid #ddd;
            text-align: center;
            font-weight: bold;
            padding: 0px;
            box-sizing: border-box;
        }

        /* Footer styling */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
            font-size: 12px;
            box-sizing: border-box;
        }

        /* Page number styling */
        .footer .page-number:before {
            content: "Page " counter(page) " / " counter(pages);
        }

        /* Main content styling */
        .content {
            margin: 0px 0px;
            /* Adjusted margins to accommodate header and footer */
        }

        .w-full {
            width: 100%;
        }

        h1,
        h2 {
            margin: 0px;
        }

        p {
            margin: 0px;
        }
        .borderTopBottom {
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }
        
        .borderB {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }
        .bottom-border td {
            line-height:20px; 
            border-bottom:solid 1px #cccccc;
        }
        .footer table {
            line-height:20px; 
            padding-bottom:20px;
        }
    </style>
</head>
@php
    // Define the createTLV function once
    $createTLV = function($tag, $value) {
        $tagByte = chr($tag);
        $lengthByte = chr(strlen($value));
        return $tagByte . $lengthByte . $value;
    };
@endphp
<body>
    @foreach($thankyous as $key => $thankyou)
    @php
        $tamount = $thankyou->ordersummary->where('type', 'total')->first();
        // Default to zero if the total is not set
        $number = isset($tamount->price) ? $tamount->price : 0;
        
        // Split the number into whole and decimal parts
        $wholePart = floor($number); // Whole number part
        $decimalPart = round(($number - $wholePart) * 100); // Two decimal places

        // Arabic words for units, tens, hundreds, thousands
        $units = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة',
                  'عر', 'أحد عشر', 'اثنا شر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 
                  'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسع عشر'];
                  
        $tens = ['', '', 'عشون', 'ثلاثون', 'أربون', 'مسون', 'ستون', 'سبعن', 'ثمانون', 'تسعو'];
        
        $hundreds = ['', 'مائة', 'مئتان', 'ثلاثمائة', 'أربعمائة', 'خمسمائة', 'ستمائة', 
                     'سبعمائة', 'ثمانائة', 'تسعماة'];

        

        // Convert the whole part and the decimal part separately
        $wholeWords = convertNumberToArabicWords($wholePart, $units, $tens, $hundreds);
        $decimalWords = convertNumberToArabicWords($decimalPart, $units, $tens, $hundreds);

        // Combine whole and decimal parts with currency labels
        $arWords = $wholeWords . ' ريال';
        if ($decimalPart > 0) {
            $arWords .= ' و ' . $decimalWords . ' هللة';
        }  
    @endphp
    <!-- Zatca barcode -->
    @php
        // Barcode generation logic in the Blade view
        $totalSummary = $thankyou->ordersummary->where('name', 'total')->first();
        $total = (float)$totalSummary->price;
        $vatRate = 0.15; // 15%
        $netAmount = $total / (1 + $vatRate);
        $VATAmount = $total - $netAmount;

        $sellerName = "Tamkeen International";
        $vatRegistrationNumber = "310180376600003";
        $timeStamp = date("Y-m-d\TH:i:s\Z", strtotime($thankyou->created_at));
        $invoiceTotal = number_format($total, 2, '.', '');
        $vatTotal = number_format($VATAmount, 2, '.', '');

        // Create TLV strings
        $fullTLV = $createTLV(1, $sellerName) .
                   $createTLV(2, $vatRegistrationNumber) .
                   $createTLV(3, $timeStamp) .
                   $createTLV(4, $invoiceTotal) .
                   $createTLV(5, $vatTotal);

        // Convert to hex and encode to base64
        $hexString = '';
        for ($i = 0; $i < strlen($fullTLV); $i++) {
            $hexString .= sprintf("%02x", ord($fullTLV[$i]));
        }
        $barcode = base64_encode(hex2bin($hexString));
    @endphp
    <table class="header w-full">
        <tr>
            <th style="text-align: right; width: 25%;">
                <img src="https://images.tamkeenstores.com.sa/media/LogoTamkeen.png"
                    style="width: 28%;" alt="tamkeenstoreslogo" />
            </th>
            <th style="text-align: center; width: 50%">
                <img src="https://images.tamkeenstores.com.sa/assets/new-media/tamkeen_Invoice_top.jpg"
                    style="width: 70%;" alt="Invoice" />
            </th>
            <th style="text-align: left; width: 25%"></th>
        </tr>
    </table>

    <div class="content">
        <table class="w-full">
            <tr>
                <th style="text-align: right; width: 50%;">
                    <h1 style="font-weight: bold; font-size: medium;">شركة تمین الدولیة للأھة المنزلیة</h1>
                    <p style="font-weight: 400; font-size: small;">الحمراء، Al Misk Street, Jeddah 23321</p>
                    <br />
                    <p style="font-family: 'cairo'; font-size: small;"><span style="font-weight: bold;">سجل
                            تجي:</span> ٤٠٣٠٠٤٩٣٤٨</p>
                    <p style="font-family: 'cairo-R' font-size: small; margin-top: 4px;"><span
                            style="font-family: 'Cairo'">يبة القيمة المضف:</span> ٣١٠١٨٠٣٧٦٦٠٠٠٠٣</p>
                </th>
                <th style="text-align: left; width: 50%">
                   @if(!is_null($barcode))
                  <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($barcode) }}"
                    style="width: 15%; float: left;" alt="Tamkeen QR Code" />
                    @endif
                </th>
            </tr>
        </table>
        <hr/>
        <table class="w-full">
            <tr>
                 <th style="text-align: right; width: 33.33%; background-color: #c2c2c2; font-size: small; font-weight: bold; padding: 6px">اسعنوان الفواتير:</th>
                 <th style="text-align: right; width: 33.33%; background-color: #c2c2c2; font-size: small; font-weight: bold; padding: 6px">عنوان الشحن:</th>
                 <th style="text-align: right; width: 33.33%; background-color: #c2c2c2; font-size: small; font-weight: bold; padding: 6px">تفاصيل المرجعي:</th>
            </tr>
            <tr>
                <td style="font-size: small; font-weight: bold;vertical-align: top;">
                   {{ $thankyou->Address->first_name ?? '' }} {{ $thankyou->Address->last_name ?? '' }}
                    <p>رقم الجوال: ٦٦<?php $phone = $thankyou->Address->phone_number ?? '' ;
                    $arabic_phone = ArabicConvertorHelper::convertToArabicNumbers($phone); echo $arabic_phone; ?>+</p>
                    <p>{{ $thankyou->UserDetail->email ?? '' }}</p>
                </td>
                <td style="font-size: small; font-weight: bold;vertical-align: top;">
                   {{ $thankyou->Address->first_name ?? '' }} {{ $thankyou->Address->last_name ?? '' }}
                    <p>رقم الجوال: ٩٦٦<?php $phone = $thankyou->Address->phone_number ?? '' ;
                    $arabic_phone = ArabicConvertorHelper::convertToArabicNumbers($phone); echo $arabic_phone; ?>+</p>
                    <p>{{ $thankyou->UserDetail->email ?? '' }}</p>
                    <p>{{ $thankyou->Address->stateData->name_arabic ?? ''}}</p>
                </td>
                <td style="font-size: small; font-weight: bold;vertical-align: top;">
                    ررقم المرعي : {{$thankyou->order_no ?? ''}}
                    <p style="margin: 10px">تاريخ المعالجة : 
                    <?php $date = $date = date('H:i d-m-Y', strtotime($thankyou->created_at ?? '')); $date = ArabicConvertorHelper::convertToArabicNumbers($date); echo $date ?></p>
                </td>
            </tr>
        </table>
        @if($thankyou->order_type == 1)
        <table class="w-full">
            <tr>
                <th style="text-align: right; width: 33.33%; background-color: #c2c2c2; font-size: small; font-weight: bold; padding: 6px">تفاصيل موقع الاستلام من المعرض:</th>
            </tr>
            <tr>
                <td style="font-size: small; font-weight: bold;vertical-align: top;">
                    <p>{{ $thankyou->warehouse->showroom_arabic ?? '' }} </p>
                    <p>{{ $thankyou->warehouse->showroom_address_arabic ?? '' }}</p>
                    <p>{{ $thankyou->warehouse->direction_button ?? '' }}</p>
                    <p>{{ $thankyou->warehouse->waybill_city_data->name_arabic ?? '' }}</p>
                    <br/>
                     <p>ملحظة: يُرجى زيارة لمعرض بعد استلام رسالة التأكيد التي تتضمن رمز تحقق مكوّنًا من ستة أرقم، وتقديم هذا الرمز إلى ممثل المعر للتحقق من هويتكم.</p>
                </td>
            </tr>
        </table>
        @endif
        <table class="w-full bottom-border" style="border-spacing: 1px;">
            <tr>
                <th style="text-align: right; background-color: #003d64; font-size: small; font-weight: bold; padding: 3px; color: #ffffff">صورة المنج</th>
                <th style="text-align: right; width: 50%; background-color: #003d64; font-size: small; font-weight: bold; padding: 3px; color: #ffffff">اسم لمنتج</th>
                <th style="text-align: right; background-color: #003d64; font-size: small; font-weight: bold; padding: 3px; color: #ffffff">كمية</th>
                <th style="text-align: right; background-color: #003d64; font-size: small; font-weight: bold; padding: 3px; color: #ffffff">سعر</th>
                <th style="text-align: right; background-color: #003d64; font-size: small; font-weight: bold; padding: 3px; color: #ffffff">إلجمالي</th>
            </tr>
            @php
                $soDetails = $thankyou->details->sortBy(function($item) {
                    return $item->unit_price == 0 ? 1 : 0;
                });
                @endphp
                @foreach($soDetails as $v)
            <tr>
                <td><img src="{{ $v->product_image }}" style="height: 60px; width: 60px;"></td>
                <td>
                    {{ $v->productData->sku ?? '' }}
                    <p>{{ $v->productData->name_arabic ?? '' }}</p>
                </td>
                <td><?php $quantity =  $v->quantity; $arabic_quantity = ArabicConvertorHelper::convertToArabicNumbers($quantity); echo $arabic_quantity;?></td>
                <td> <?php $p_price = number_format($v->unit_price) ?? '' ; $arabic_price = ArabicConvertorHelper::convertToArabicNumbers($p_price); echo $arabic_price;?>  <span style="font-size: 12px;">رس</td>
                <td> <?php $p_quan = number_format($v->unit_price * $v->quantity) ?? '' ; $arabic_quan = ArabicConvertorHelper::convertToArabicNumbers($p_quan); echo $arabic_quan;?> <span style="font-size: 12px;">رس</td>
            </tr>
            @endforeach
        </table>
        <table style="width: 100%; margin-top:40px">
            <tr style="width: 50%">
                <td style="">
                    طريقة الدفع:  
                    @if($thankyou->paymentmethod == 'tabby')
                        تاى
                    @elseif($thankyou->paymentmethod == 'applepay')
                       ابل باي  
                    @elseif($thankyou->paymentmethod == 'hyperpay')
                        فرط الدفع
                    @elseif($thankyou->paymentmethod == 'tasheel')
                        تسهيل
                    @elseif($thankyou->paymentmethod == 'tamara')
                        تمارا
                    @elseif($thankyou->paymentmethod == 'cod')
                        نقدارم
                    @elseif($thankyou->paymentmethod == 'madfu')
                        مفوع
                    @elseif($thankyou->paymentmethod == 'mispay')
                       دفع خاطئ
                    @elseif($thankyou->paymentmethod == 'clickpay')
                       ادفع عبر البطاقة
                    @elseif($thankyou->paymentmethod == 'madapay')
                        مدى الدفع
                    @else
                        {{$thankyou->paymentmethod }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
        <table style="width: 100%; margin-top: 20px">
            @foreach ($thankyou->ordersummary as $summary)
            <tr style="width: 100%">
                <td style="border: 1px solid #e5e7eb;float:right;">{{$summary->name_arabic}}</td>
                <td style="border: 1px solid #e5e7eb;float:right;"><?php $total = $summary->price; $arabictotal = ArabicConvertorHelper::convertToArabicNumbers($total); echo $arabictotal;?> <span style="font-weight: 600;float: right;">رس</span></td>
                @if($summary->type == 'total')
                    <td style="border: 1px solid #e5e7eb; width: 50%;float: left">فقط {{ $arWords }}</td>
                @endif
            </tr>
            @endforeach
            <!--<tr></tr>-->
        </table>
    </div>

    <div class="footer">
        <table class="borderTopBottom w-full" style="margin-bottom: 10px;">
            <tr style="padding: 0px; margin: 0px">
                <th style="font-size: small" align="right">شركة مكين الدولية للاهزة المنزلية ص.ب. ٣٠٩٣٤، جد ٢١٤٨٧ امملكة العربية العودية</th>
                <th style="font-size: small" align="left">الرقم المود: <span>٨٠٢٤٤٤٤٦٤</span></th>
            </tr>
        </table>
        <span class="page-number">
            {PAGENO} / {nb}
        </span>
    </div>
    <!--Another Page-->
    <div class="content" style="page-break-before:always;">
        <img src="https://images.tamkeenstores.com.sa/media/termsConditionOnline.png"
                    style="width: 100%;" alt="TermsandConditions Online" />
    </div>
    
    <div class="footer">
        <table class="borderTopBottom w-full" style="margin-top: 35px; margin-bottom: 10px;">
            <tr>
                <th style="font-size: small" align="right">شكر لتسوقكم من شرة مكين الدولية للأهزة المنزلية</th>
                <th style="font-size: small" align="left">contact@tamkeenstores.com.sa</th>
            </tr>
        </table>
        <span class="page-number" style="line-height: 12px">{PAGENO} / {nb}</span>
    </div>
    @endforeach
</body>
</html>