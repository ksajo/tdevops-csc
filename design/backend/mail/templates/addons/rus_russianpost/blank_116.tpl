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

<body style="width: 293mm; height: 210mm;">
    <div style="top: {$addons.rus_russianpost.116_top}mm; left: {$addons.rus_russianpost.116_left}mm; width: {$addons.rus_russianpost.116_list_width}mm; height: {$addons.rus_russianpost.116_list_height}mm; position: relative;" >
        {if $data.print_bg == 'Y'}
            <img style="width: 293mm; height: 206mm;" src="{$images_dir}/addons/rus_russianpost/blank_116.jpg" />
        {/if}

        {if !empty($data.not_total) && $data.not_total == 'Y'}
            <span style="position: absolute; height: 5mm; width: 67mm; top: 45.5mm; left: 19mm; font: 7pt 'Arial';">{$data.t_declared_kop}</span>
        {/if}
        {if !empty($data.total_imposed) && $data.imposed_total == 'Y'}
            <span style="position: absolute; height: 5mm; width: 67mm; top: 54.5mm; left: 19mm; font: 7pt 'Arial';">{$data.total_imposed}</span>
        {/if}

        <span style="position: absolute; height: 10mm; width: 65mm; top: 64mm; left: 20mm; font: 11pt 'Arial'; text-indent: 10mm;">{$data.from_whom} {$data.from_whom2}</span>
        <span style="position: absolute; height: 10mm; width: 115mm; top: 75mm; left: 19mm; font: 11pt 'Arial'; text-indent: 14mm; line-height: 14pt;">{$data.sender_address} {$data.sender_address2}</span>

        <span style="position: absolute; height: 4mm; width: 28mm; top: 80mm; left: 105mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            <span style="position: absolute; left: 0mm;">{$data.from_index.0}</span>
            <span style="position: absolute; left: 5mm;">{$data.from_index.1}</span>
            <span style="position: absolute; left: 10mm;">{$data.from_index.2}</span>
            <span style="position: absolute; left: 15mm;">{$data.from_index.3}</span>
            <span style="position: absolute; left: 20mm;">{$data.from_index.4}</span>
            <span style="position: absolute; left: 25mm;">{$data.from_index.5}</span>
        </span>

        <span style="position: absolute; height: 4mm; width: 100mm; top: 85mm; left: 35mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fiz_fio} {$data.fiz_fio2}{/if}</span>
        <span style="position: absolute; height: 13mm; width: 115mm; top: 89mm; left: 20mm; font: 11pt 'Arial'; line-height: 15pt; text-indent: 13mm;">{if $data.sender == '1'}{$data.where} {$data.where2}{else}{$data.fiz_address} {$data.fiz_address2}{/if}</span>

        <span style="position: absolute; height: 5mm; width: 28mm; top: 95mm; left: 104mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            {if $data.sender == '1'}
                <span style="position: absolute; left: 0mm;">{$data.index.0}</span>
                <span style="position: absolute; left: 5mm;">{$data.index.1}</span>
                <span style="position: absolute; left: 10mm;">{$data.index.2}</span>
                <span style="position: absolute; left: 16mm;">{$data.index.3}</span>
                <span style="position: absolute; left: 21mm;">{$data.index.4}</span>
                <span style="position: absolute; left: 26mm;">{$data.index.5}</span>
            {else}
                <span style="position: absolute; left: 0mm;">{$data.fiz_index.0}</span>
                <span style="position: absolute; left: 5mm;">{$data.fiz_index.1}</span>
                <span style="position: absolute; left: 10mm;">{$data.fiz_index.2}</span>
                <span style="position: absolute; left: 16mm;">{$data.fiz_index.3}</span>
                <span style="position: absolute; left: 21mm;">{$data.fiz_index.4}</span>
                <span style="position: absolute; left: 26mm;">{$data.fiz_index.5}</span>
            {/if}
        </span>

        <span style="position: absolute; height: 4mm; width: 20mm; top: 109mm; left: 41mm; font: 11pt 'Arial';">{$data.fiz_doc}</span>
        <span style="position: absolute; height: 4mm; width: 12mm; top: 109mm; left: 72mm; font: 11pt 'Arial';">{$data.fiz_doc_serial}</span>
        <span style="position: absolute; height: 4mm; width: 14mm; top: 109mm; left: 89mm; font: 11pt 'Arial';">{$data.fiz_doc_number}</span>
        <span style="position: absolute; height: 4mm; width: 10mm; top: 109mm; left: 112mm; font: 10pt 'Arial';">{$data.fiz_doc_date}</span>
        <span style="position: absolute; height: 4mm; width: 5mm; top: 109mm; left: 125mm; font: 10pt 'Arial';">{$data.fiz_doc_date2}</span>
        <span style="position: absolute; height: 4mm; width: 110mm; top: 115mm; left: 20mm; font: 11pt 'Arial';">{$data.fiz_doc_creator}</span>

        {if !empty($data.imposed_total) && $data.imposed_total == 'Y'}
            <span style="position: absolute; height: 4mm; width: 25mm; top: 163mm; left: 40mm; font: 11pt 'Arial';">{$data.total_cen}</span>
            <span style="position: absolute; height: 4mm; width: 25mm; top: 163mm; left: 100mm; font: 11pt 'Arial';">{$data.total_cod}</span>
        {/if}

        <span style="position: absolute; height: 10mm; width: 100mm; top: 170mm; left: 30mm; font: 11pt 'Arial';">{$data.from_whom} {$data.from_whom2}</span>
        <span style="position: absolute; height: 10mm; width: 112mm; top: 175mm; left: 19mm; font: 11pt 'Arial'; text-indent: 14mm; line-height: 14pt;">{$data.sender_address} {$data.sender_address2}</span>

        <span style="position: absolute; height: 5mm; width: 28mm; top: 180mm; left: 102mm; font: 11pt 'Arial'; letter-spacing: 7.6pt;">
            <span style="position: absolute; left: 0mm;">{$data.from_index.0}</span>
            <span style="position: absolute; left: 5mm;">{$data.from_index.1}</span>
            <span style="position: absolute; left: 10mm;">{$data.from_index.2}</span>
            <span style="position: absolute; left: 15mm;">{$data.from_index.3}</span>
            <span style="position: absolute; left: 20mm;">{$data.from_index.4}</span>
            <span style="position: absolute; left: 25mm;">{$data.from_index.5}</span>
        </span>
    </div>
</body>
</html>
