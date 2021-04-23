<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{literal}
<style type="text/css" media="screen,print">

body,p,div,td {
    color: #000000;
    font: 12px Arial;
}
body {
    padding: 0;
    margin: 0;
}
a, a:link, a:visited, a:hover, a:active {
    color: #000000;
    text-decoration: underline;
}
a:hover {
    text-decoration: none;
}
</style>

{/literal}
</head>

<body style="width: 210mm; height: 293mm;">
    <div style="top: {$addons.rus_russianpost.112_top}mm; left: {$addons.rus_russianpost.112_left}mm; width: {$addons.rus_russianpost.112_list_width}mm; height: {$addons.rus_russianpost.112_list_height}mm; position: relative;" >
        {if $data.print_bg == 'Y'}
            <img style="width: 210mm; height: 293mm;" src="{$images_dir}/addons/rus_russianpost/blank_112ep.jpg" />
        {/if}

        {if !empty($data.imposed_total) && $data.imposed_total == 'Y'}
            <span style="position: absolute; height: 5mm; width: 5mm; top: 70mm; left: 17mm; text-align: center; font: 30pt 'Arial';">&#xD7;</span>
            <span style="position: absolute; height: 5mm; width: 15mm; top: 66.5mm; left: 19mm; text-align: center; font: 11pt 'Arial';">{$data.imposed_rub}</span>
            <span style="position: absolute; height: 5mm; width: 7mm; top: 66.5mm; left: 42mm; text-align: center; font: 11pt 'Arial';">{$data.imposed_kop}</span>
            <span style="position: absolute; height: 5mm; width: 125mm; top: 60mm; left: 60mm; text-align: center; font: 10pt 'Arial';">{$data.t_imposed}</span>
        {/if}

        {if $data.sms_for_sender == 'Y'}
            <div style="position: absolute; height: 4mm; width: 35mm; top: 71mm; left: 151mm; font: 11pt 'Arial'; margin:0;">
                <span style="position: absolute; left: 0mm;">{$data.company_phone.0}</span>
                <span style="position: absolute; left: 4mm;">{$data.company_phone.1}</span>
                <span style="position: absolute; left: 7mm;">{$data.company_phone.2}</span>
                <span style="position: absolute; left: 11mm;">{$data.company_phone.3}</span>
                <span style="position: absolute; left: 15mm;">{$data.company_phone.4}</span>
                <span style="position: absolute; left: 18mm;">{$data.company_phone.5}</span>
                <span style="position: absolute; left: 22mm;">{$data.company_phone.6}</span>
                <span style="position: absolute; left: 26mm;">{$data.company_phone.7}</span>
                <span style="position: absolute; left: 29mm;">{$data.company_phone.8}</span>
                <span style="position: absolute; left: 32mm;">{$data.company_phone.9}</span>
            </div>
        {/if}

        {if $data.sms_for_recepient == 'Y'}
            <div style="position: absolute; height: 4mm; width: 35mm; top: 77mm; left: 151mm; font: 11pt 'Arial'; margin:0;">
                <span style="position: absolute; left: 0mm;">{$data.recipient_phone.0}</span>
                <span style="position: absolute; left: 4mm;">{$data.recipient_phone.1}</span>
                <span style="position: absolute; left: 7mm;">{$data.recipient_phone.2}</span>
                <span style="position: absolute; left: 11mm;">{$data.recipient_phone.3}</span>
                <span style="position: absolute; left: 15mm;">{$data.recipient_phone.4}</span>
                <span style="position: absolute; left: 18mm;">{$data.recipient_phone.5}</span>
                <span style="position: absolute; left: 22mm;">{$data.recipient_phone.6}</span>
                <span style="position: absolute; left: 26mm;">{$data.recipient_phone.7}</span>
                <span style="position: absolute; left: 29mm;">{$data.recipient_phone.8}</span>
                <span style="position: absolute; left: 32mm;">{$data.recipient_phone.9}</span>
            </div>
            <span style="position: absolute; height: 5mm; width: 4mm; top: 75mm; left: 59mm; text-align: center; font: 15pt 'Arial';">&#xD7;</span>
        {/if}

        <span style="position: absolute; height: 5mm; width: 160mm; top: 83mm; left: 27mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fiz_fio} {$data.fiz_fio2}{/if}</span>
        <span style="position: absolute; height: 13mm; width: 160mm; top: 89mm; left: 27mm; font: 11pt 'Arial'; line-height: 15pt;">{if $data.sender == '1'}{$data.where} {$data.where2}{else}{$data.fiz_address} {$data.fiz_address2}{/if}</span>

        <span style="position: absolute; height: 5mm; width: 25mm; top: 96mm; left: 160mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            {if $data.sender == '1'}
                <span style="position: absolute; left: 0mm;">{$data.index.0}</span>
                <span style="position: absolute; left: 5mm;">{$data.index.1}</span>
                <span style="position: absolute; left: 9mm;">{$data.index.2}</span>
                <span style="position: absolute; left: 14mm;">{$data.index.3}</span>
                <span style="position: absolute; left: 19mm;">{$data.index.4}</span>
                <span style="position: absolute; left: 23mm;">{$data.index.5}</span>
            {else}
                <span style="position: absolute; left: 0mm;">{$data.fiz_index.0}</span>
                <span style="position: absolute; left: 5mm;">{$data.fiz_index.1}</span>
                <span style="position: absolute; left: 9mm;">{$data.fiz_index.2}</span>
                <span style="position: absolute; left: 14mm;">{$data.fiz_index.3}</span>
                <span style="position: absolute; left: 19mm;">{$data.fiz_index.4}</span>
                <span style="position: absolute; left: 23mm;">{$data.fiz_index.5}</span>
            {/if}
        </span>

        <div style="position: absolute; height: 4.5mm; width: 55mm; top: 104mm; left: 39mm; font: 11pt 'Arial'; margin: 0;">
            <span style="position: absolute; left: 0mm;">{$data.text1.0}</span>
            <span style="position: absolute; left: 5mm;">{$data.text1.1}</span>
            <span style="position: absolute; left: 9mm;">{$data.text1.2}</span>
            <span style="position: absolute; left: 13mm;">{$data.text1.3}</span>
            <span style="position: absolute; left: 17mm;">{$data.text1.4}</span>
            <span style="position: absolute; left: 22mm;">{$data.text1.5}</span>
            <span style="position: absolute; left: 26mm;">{$data.text1.6}</span>
            <span style="position: absolute; left: 30mm;">{$data.text1.7}</span>
            <span style="position: absolute; left: 34mm;">{$data.text1.8}</span>
            <span style="position: absolute; left: 38mm;">{$data.text1.9}</span>
            <span style="position: absolute; left: 43mm;">{$data.text1.10}</span>
            <span style="position: absolute; left: 47mm;">{$data.text1.11}</span>
            <span style="position: absolute; left: 52mm;">{$data.text1.12}</span>
            <span style="position: absolute; left: 56mm;">{$data.text1.13}</span>
            <span style="position: absolute; left: 60mm;">{$data.text1.14}</span>
            <span style="position: absolute; left: 64mm;">{$data.text1.15}</span>
            <span style="position: absolute; left: 69mm;">{$data.text1.16}</span>
            <span style="position: absolute; left: 72mm;">{$data.text1.17}</span>
            <span style="position: absolute; left: 77mm;">{$data.text1.18}</span>
            <span style="position: absolute; left: 81mm;">{$data.text1.19}</span>
            <span style="position: absolute; left: 85mm;">{$data.text1.20}</span>
            <span style="position: absolute; left: 89mm;">{$data.text1.21}</span>
            <span style="position: absolute; left: 94mm;">{$data.text1.22}</span>
            <span style="position: absolute; left: 98mm;">{$data.text1.23}</span>
            <span style="position: absolute; left: 102mm;">{$data.text1.24}</span>
            <span style="position: absolute; left: 106mm;">{$data.text1.25}</span>
            <span style="position: absolute; left: 110mm;">{$data.text1.26}</span>
            <span style="position: absolute; left: 115mm;">{$data.text1.27}</span>
            <span style="position: absolute; left: 119mm;">{$data.text1.28}</span>
            <span style="position: absolute; left: 123mm;">{$data.text1.29}</span>
            <span style="position: absolute; left: 127mm;">{$data.text1.30}</span>
            <span style="position: absolute; left: 132mm;">{$data.text1.31}</span>
            <span style="position: absolute; left: 136mm;">{$data.text1.32}</span>
            <span style="position: absolute; left: 140mm;">{$data.text1.33}</span>
            <span style="position: absolute; left: 144mm;">{$data.text1.34}</span>
        </div>

        <div style="position: absolute; height: 4.5mm; width: 55mm; top: 111mm; left: 39mm; font: 11pt 'Arial'; margin: 0;">
            <span style="position: absolute; left: 0mm;">{$data.text2.0}</span>
            <span style="position: absolute; left: 5mm;">{$data.text2.1}</span>
            <span style="position: absolute; left: 9mm;">{$data.text2.2}</span>
            <span style="position: absolute; left: 13mm;">{$data.text2.3}</span>
            <span style="position: absolute; left: 17mm;">{$data.text2.4}</span>
            <span style="position: absolute; left: 22mm;">{$data.text2.5}</span>
            <span style="position: absolute; left: 26mm;">{$data.text2.6}</span>
            <span style="position: absolute; left: 30mm;">{$data.text2.7}</span>
            <span style="position: absolute; left: 34mm;">{$data.text2.8}</span>
            <span style="position: absolute; left: 38mm;">{$data.text2.9}</span>
            <span style="position: absolute; left: 43mm;">{$data.text2.10}</span>
            <span style="position: absolute; left: 47mm;">{$data.text2.11}</span>
            <span style="position: absolute; left: 52mm;">{$data.text2.12}</span>
            <span style="position: absolute; left: 56mm;">{$data.text2.13}</span>
            <span style="position: absolute; left: 60mm;">{$data.text2.14}</span>
            <span style="position: absolute; left: 64mm;">{$data.text2.15}</span>
            <span style="position: absolute; left: 69mm;">{$data.text2.16}</span>
            <span style="position: absolute; left: 72mm;">{$data.text2.17}</span>
            <span style="position: absolute; left: 77mm;">{$data.text2.18}</span>
            <span style="position: absolute; left: 81mm;">{$data.text2.19}</span>
            <span style="position: absolute; left: 85mm;">{$data.text2.20}</span>
            <span style="position: absolute; left: 89mm;">{$data.text2.21}</span>
            <span style="position: absolute; left: 94mm;">{$data.text2.22}</span>
            <span style="position: absolute; left: 98mm;">{$data.text2.23}</span>
            <span style="position: absolute; left: 102mm;">{$data.text2.24}</span>
            <span style="position: absolute; left: 106mm;">{$data.text2.25}</span>
            <span style="position: absolute; left: 110mm;">{$data.text2.26}</span>
            <span style="position: absolute; left: 115mm;">{$data.text2.27}</span>
            <span style="position: absolute; left: 119mm;">{$data.text2.28}</span>
            <span style="position: absolute; left: 123mm;">{$data.text2.29}</span>
            <span style="position: absolute; left: 127mm;">{$data.text2.30}</span>
            <span style="position: absolute; left: 132mm;">{$data.text2.31}</span>
            <span style="position: absolute; left: 136mm;">{$data.text2.32}</span>
            <span style="position: absolute; left: 140mm;">{$data.text2.33}</span>
            <span style="position: absolute; left: 144mm;">{$data.text2.34}</span>
        </div>

        <div style="position: absolute; height: 5mm; width: 50mm; top: 121.5mm; left: 27mm; font: 11pt 'Arial'; margin: 0;">
            <span style="position: absolute; left: 0mm;">{$data.inn.0}</span>
            <span style="position: absolute; left: 5mm;">{$data.inn.1}</span>
            <span style="position: absolute; left: 9mm;">{$data.inn.2}</span>
            <span style="position: absolute; left: 13mm;">{$data.inn.3}</span>
            <span style="position: absolute; left: 18mm;">{$data.inn.4}</span>
            <span style="position: absolute; left: 22mm;">{$data.inn.5}</span>
            <span style="position: absolute; left: 26mm;">{$data.inn.6}</span>
            <span style="position: absolute; left: 30mm;">{$data.inn.7}</span>
            <span style="position: absolute; left: 35mm;">{$data.inn.8}</span>
            <span style="position: absolute; left: 39mm;">{$data.inn.9}</span>
            <span style="position: absolute; left: 43mm;">{$data.inn.10}</span>
            <span style="position: absolute; left: 48mm;">{$data.inn.11}</span>
        </div>
        <div style="position: absolute; height: 5mm; width: 85mm; top: 121.5mm; left: 102.5mm; font: 11pt 'Arial'; margin: 0;">
            <span style="position: absolute; left: 0mm;">{$data.kor.0}</span>
            <span style="position: absolute; left: 4mm;">{$data.kor.1}</span>
            <span style="position: absolute; left: 9mm;">{$data.kor.2}</span>
            <span style="position: absolute; left: 13mm;">{$data.kor.3}</span>
            <span style="position: absolute; left: 18mm;">{$data.kor.4}</span>
            <span style="position: absolute; left: 22mm;">{$data.kor.5}</span>
            <span style="position: absolute; left: 26mm;">{$data.kor.6}</span>
            <span style="position: absolute; left: 30mm;">{$data.kor.7}</span>
            <span style="position: absolute; left: 34mm;">{$data.kor.8}</span>
            <span style="position: absolute; left: 38mm;">{$data.kor.9}</span>
            <span style="position: absolute; left: 43mm;">{$data.kor.10}</span>
            <span style="position: absolute; left: 47mm;">{$data.kor.11}</span>
            <span style="position: absolute; left: 51mm;">{$data.kor.12}</span>
            <span style="position: absolute; left: 55mm;">{$data.kor.13}</span>
            <span style="position: absolute; left: 60mm;">{$data.kor.14}</span>
            <span style="position: absolute; left: 64mm;">{$data.kor.15}</span>
            <span style="position: absolute; left: 68mm;">{$data.kor.16}</span>
            <span style="position: absolute; left: 72mm;">{$data.kor.17}</span>
            <span style="position: absolute; left: 77mm;">{$data.kor.18}</span>
            <span style="position: absolute; left: 81mm;">{$data.kor.19}</span>
        </div>
        <span style="position: absolute; height: 5mm; width: 130mm; top: 127mm; left: 55mm; font: 11pt 'Arial';">{$data.bank}</span>
        <div style="position: absolute; height: 5mm; width: 85mm; top: 133mm; left: 34mm; font: 11pt 'Arial'; margin: 0;">
            <span style="position: absolute; left: 0mm;">{$data.ras.0}</span>
            <span style="position: absolute; left: 4mm;">{$data.ras.1}</span>
            <span style="position: absolute; left: 8mm;">{$data.ras.2}</span>
            <span style="position: absolute; left: 13mm;">{$data.ras.3}</span>
            <span style="position: absolute; left: 17mm;">{$data.ras.4}</span>
            <span style="position: absolute; left: 21mm;">{$data.ras.5}</span>
            <span style="position: absolute; left: 26mm;">{$data.ras.6}</span>
            <span style="position: absolute; left: 30mm;">{$data.ras.7}</span>
            <span style="position: absolute; left: 34mm;">{$data.ras.8}</span>
            <span style="position: absolute; left: 38mm;">{$data.ras.9}</span>
            <span style="position: absolute; left: 43mm;">{$data.ras.10}</span>
            <span style="position: absolute; left: 47mm;">{$data.ras.11}</span>
            <span style="position: absolute; left: 51mm;">{$data.ras.12}</span>
            <span style="position: absolute; left: 55mm;">{$data.ras.13}</span>
            <span style="position: absolute; left: 60mm;">{$data.ras.14}</span>
            <span style="position: absolute; left: 64mm;">{$data.ras.15}</span>
            <span style="position: absolute; left: 68mm;">{$data.ras.16}</span>
            <span style="position: absolute; left: 73mm;">{$data.ras.17}</span>
            <span style="position: absolute; left: 77mm;">{$data.ras.18}</span>
            <span style="position: absolute; left: 81mm;">{$data.ras.19}</span>
        </div>
        <div style="position: absolute; height: 5mm; width: 38mm; top: 133mm; left: 149mm; font: 11pt 'Arial'; margin: 0;">
            <span style="position: absolute; left: 0mm;">{$data.bik.0}</span>
            <span style="position: absolute; left: 4mm;">{$data.bik.1}</span>
            <span style="position: absolute; left: 9mm;">{$data.bik.2}</span>
            <span style="position: absolute; left: 13mm;">{$data.bik.3}</span>
            <span style="position: absolute; left: 17mm;">{$data.bik.4}</span>
            <span style="position: absolute; left: 22mm;">{$data.bik.5}</span>
            <span style="position: absolute; left: 26mm;">{$data.bik.6}</span>
            <span style="position: absolute; left: 30mm;">{$data.bik.7}</span>
            <span style="position: absolute; left: 34mm;">{$data.bik.8}</span>
        </div>

        <span style="position: absolute; height: 5mm; width: 150mm; top: 139mm; left: 35mm; font: 11pt 'Arial';">{$data.from_whom} {$data.from_whom2}</span>
        <span style="position: absolute; height: 10mm; width: 170mm; top: 145.5mm; left: 17mm; font: 11pt 'Arial'; text-indent: 35mm; line-height: 14pt;">{$data.sender_address} {$data.sender_address2}</span>

        <span style="position: absolute; height: 4mm; width: 23mm; top: 151mm; left: 160mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            <span style="position: absolute; left: 0mm;">{$data.from_index.0}</span>
            <span style="position: absolute; left: 5mm;">{$data.from_index.1}</span>
            <span style="position: absolute; left: 10mm;">{$data.from_index.2}</span>
            <span style="position: absolute; left: 14mm;">{$data.from_index.3}</span>
            <span style="position: absolute; left: 19mm;">{$data.from_index.4}</span>
            <span style="position: absolute; left: 23mm;">{$data.from_index.5}</span>
        </span>
    </div>
</body>
</html>
