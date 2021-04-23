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

<body style="width: 297mm; height: 210mm;">
    <div style="top: {$addons.rus_russianpost.7a_top}mm; left: {$addons.rus_russianpost.7a_left}mm; width: 198mm; height: 141mm; position: relative;" >
        {if $data.print_bg == 'Y'}
            <img style="width: 198mm; height: 141mm;" src="{$images_dir}/addons/rus_russianpost/blank_7a.jpg" />
        {/if}

        {if $data.type_mailing == 'package'}
            <span style="position: absolute; height: 5mm; width: 5mm; top: 15.5mm; left: 59.5mm; text-align: center; font: 9pt 'Arial';">&#xD7;</span>
        {else}
            <span style="position: absolute; height: 5mm; width: 5mm; top: 19mm; left: 59.5mm; text-align: center; font: 9pt 'Arial';">&#xD7;</span>
        {/if}

        {if !empty($data.not_total) && $data.not_total == 'Y'}
            <span style="position: absolute; height: 5mm; width: 5mm; top: 15mm; left: 99mm; text-align: center; font: 9pt 'Arial';">&#xD7;</span>
            <span style="position: absolute; height: 5mm; width: 85mm; top: 44mm; left: 100mm; text-align: center; font: 11pt 'Arial';">{$data.total_declared}</span>
        {/if}

        {if !empty($data.imposed_total) && $data.imposed_total == 'Y'}
            <span style="position: absolute; height: 5mm; width: 5mm; top: 18.5mm; left: 99mm; text-align: center; font: 9pt 'Arial';">&#xD7;</span>
            <span style="position: absolute; height: 5mm; width: 85mm; top: 55mm; left: 100mm; text-align: center; font: 11pt 'Arial';">{$data.total_imposed}</span>
        {/if}

        <span style="position: absolute; height: 9mm; width: 70mm; top: 50mm; left: 23mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fiz_fio} {$data.fiz_fio2}{/if}</span>
        <span style="position: absolute; height: 15mm; width: 70mm; top: 66mm; left: 17mm; font: 11pt 'Arial'; line-height: 15pt;">{if $data.sender == '1'}{$data.where} {$data.where2}{else}{$data.fiz_address} {$data.fiz_address2}{/if}</span>
        {if $data.sms_for_sender == 'Y'}
            <div style="position: absolute; height: 4mm; width: 40mm; top: 86mm; left: 23mm; font: 11pt 'Arial'; margin:0;">
                <span style="position: absolute; left: 0mm;">{$data.company_phone.0}</span>
                <span style="position: absolute; left: 4mm;">{$data.company_phone.1}</span>
                <span style="position: absolute; left: 8mm;">{$data.company_phone.2}</span>
                <span style="position: absolute; left: 13mm;">{$data.company_phone.3}</span>
                <span style="position: absolute; left: 17mm;">{$data.company_phone.4}</span>
                <span style="position: absolute; left: 22mm;">{$data.company_phone.5}</span>
                <span style="position: absolute; left: 26mm;">{$data.company_phone.6}</span>
                <span style="position: absolute; left: 30mm;">{$data.company_phone.7}</span>
                <span style="position: absolute; left: 34mm;">{$data.company_phone.8}</span>
                <span style="position: absolute; left: 38mm;">{$data.company_phone.9}</span>
            </div>
            <span style="position: absolute; height: 5mm; width: 5mm; top: 90mm; left: 17.5mm; text-align: center; font: 9pt 'Arial';">&#xD7;</span>
        {/if}
        <span style="position: absolute; height: 4mm; width: 23mm; top: 86mm; left: 70mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            {if $data.sender == '1'}
                <span style="position: absolute; left: 0mm;">{$data.index.0}</span>
                <span style="position: absolute; left: 4mm;">{$data.index.1}</span>
                <span style="position: absolute; left: 8mm;">{$data.index.2}</span>
                <span style="position: absolute; left: 12mm;">{$data.index.3}</span>
                <span style="position: absolute; left: 16mm;">{$data.index.4}</span>
                <span style="position: absolute; left: 20mm;">{$data.index.5}</span>
            {else}
                <span style="position: absolute; left: 0mm;">{$data.fiz_index.0}</span>
                <span style="position: absolute; left: 4mm;">{$data.fiz_index.1}</span>
                <span style="position: absolute; left: 8mm;">{$data.fiz_index.2}</span>
                <span style="position: absolute; left: 12mm;">{$data.fiz_index.3}</span>
                <span style="position: absolute; left: 16mm;">{$data.fiz_index.4}</span>
                <span style="position: absolute; left: 20mm;">{$data.fiz_index.5}</span>
            {/if}
        </span>

        <span style="position: absolute; height: 5mm; width: 80mm; top: 78mm; left: 105mm; font: 11pt 'Arial';">{$data.from_whom} {$data.from_whom2}</span>
        <span style="position: absolute; height: 15mm; width: 80mm; top: 94mm; left: 100mm; font: 11pt 'Arial'; line-height: 14pt;">{$data.sender_address} {$data.sender_address2}</span>
        {if $data.sms_for_recepient == 'Y'}
            <div style="position: absolute; height: 4mm; width: 40mm; top: 113.5mm; left: 106mm; font: 11pt 'Arial'; margin:0;">
                <span style="position: absolute; left: 0mm;">{$data.recipient_phone.0}</span>
                <span style="position: absolute; left: 4mm;">{$data.recipient_phone.1}</span>
                <span style="position: absolute; left: 8mm;">{$data.recipient_phone.2}</span>
                <span style="position: absolute; left: 13mm;">{$data.recipient_phone.3}</span>
                <span style="position: absolute; left: 17mm;">{$data.recipient_phone.4}</span>
                <span style="position: absolute; left: 22mm;">{$data.recipient_phone.5}</span>
                <span style="position: absolute; left: 26mm;">{$data.recipient_phone.6}</span>
                <span style="position: absolute; left: 30mm;">{$data.recipient_phone.7}</span>
                <span style="position: absolute; left: 34mm;">{$data.recipient_phone.8}</span>
                <span style="position: absolute; left: 38mm;">{$data.recipient_phone.9}</span>
            </div>
            <span style="position: absolute; height: 5mm; width: 5mm; top: 118mm; left: 100.5mm; text-align: center; font: 9pt 'Arial';">&#xD7;</span>
        {/if}
        <span style="position: absolute; height: 4mm; width: 23mm; top: 113.5mm; left: 153mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            <span style="position: absolute; left: 0mm;">{$data.from_index.0}</span>
            <span style="position: absolute; left: 4mm;">{$data.from_index.1}</span>
            <span style="position: absolute; left: 8mm;">{$data.from_index.2}</span>
            <span style="position: absolute; left: 12mm;">{$data.from_index.3}</span>
            <span style="position: absolute; left: 16mm;">{$data.from_index.4}</span>
            <span style="position: absolute; left: 20mm;">{$data.from_index.5}</span>
        </span>
    </div>
</body>
</html>
