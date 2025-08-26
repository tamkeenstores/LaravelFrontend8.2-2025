@slot('header')
@endslot
<style>
body, body *:not(html):not(style):not(br):not(tr):not(code) {
    font-family: Avenir, Helvetica, sans-serif;
    box-sizing: border-box;
}
</style>
<body dir="rtl" style="font-family:arial, 'helvetica neue', helvetica, sans-serif;">
    <tr>
        <td>
            <table width="90%" style="background: #fff; padding: 20px; margin-bottom:10px;" align="center">
                <tr>
                    <p style="padding: 0px;margin: 0px;text-align: right;font-family: 'Helvetica, Arial, sans-serif;">الساده الكرام/ المحترمين،</p>
                    <p style="margin-bottom: 0px;margin-top: 30px;text-align: right;font-family: Helvetica, sans-serif;">نتمنى ان تكونوا بخير</p>
                    <p style="margin-bottom: 0px;margin-top: 8px;text-align: right;">الطلبات المذكورة ادناه اجمالي
                        الطلبات المذكورة ادناه هي اجمالي الطلبات الخاصة بالاون لاين
                        {{
                        Carbon\Carbon::parse($currenttime)->format('dM, Y H:i:s A') }}
                        </p>
                    <table>
                        <tr>
                            <th style="border: 1px solid #000;padding: 2px;font-size: 13px;text-align: center;">اجمالي طلبات المستلمة</th>
                            <th style="border: 1px solid #000;padding: 2px;font-size: 13px;text-align: center;">اجمالي طلبات التي تم سحبها الي  انفور</th>
                            <th style="border: 1px solid #000;padding: 2px;font-size: 13px;text-align: center;">الطلبات المتبقية</th>
                        </tr>
                        <tr>
                            <td
                                style="border: 1px solid #000;padding: 2px;font-size: 13px;font-weight: bold;text-align: center;">
                                {{$orderscount }}</td>
                            <td
                                style="border: 1px solid #000;padding: 2px;font-size: 13px;font-weight: bold;text-align: center;">
                                {{$orderfetchcount }}</td>
                            <td
                                style="border: 1px solid #000;padding: 2px;font-size: 13px;font-weight: bold;text-align: center;">
                                {{$orderscount - $orderfetchcount }}</td>
                        </tr>
                    </table>
                    <p style="margin-bottom: 0px;margin-top: 4px;font-size: 12px;text-align: right;">تم ارفاق الملف لكل طلب في الايميل</p>
                    <p style="margin-bottom: 0px;padding-bottom: 0px;margin-top: 50px;text-align: right;">شكرا لكم</p>
                    <p style="margin: 0px;text-align: right;"><b>داش بورد موقع  تمكين</b></p>
                    <p style="margin: 0px;font-size: 12px;margin-top: 6px;text-align: right;">في حال استفسارات متعلقة بالدعم التقني يرجى التواصل مع</p>
                    <small style="margin: 0px;text-align: right;">هذا البريد الإلكتروني هو بريد إلكتروني تم إنشاؤه بواسطة النظام</small>
                </tr>
            </table>
        </td>
    </tr>
</body>
@slot('footer')
@endslot