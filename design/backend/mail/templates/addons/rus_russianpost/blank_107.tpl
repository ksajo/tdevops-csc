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

<body style="width: 297mm; height: 205mm;">
    <div style="top: {$addons.rus_russianpost.107_top}mm; left: {$addons.rus_russianpost.107_left}mm; width: {$addons.rus_russianpost.107_width}mm; height: {$addons.rus_russianpost.107_height}mm; position: relative;" >
        {if $data.print_bg == 'Y'}
            <img style="width: 293mm; height: 205mm;" src="{$images_dir}/addons/rus_russianpost/blank_107.jpg" />
        {/if}

        <span style="position: absolute; height: 10mm; width: 80mm; top: 142mm; left: 20mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fio} {$data.fio2}{/if}</span>
        <span style="position: absolute; height: 10mm; width: 80mm; top: 142mm; left: 165mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fio} {$data.fio2}{/if}</span>

        {assign var="p_size" value=0}
        {assign var="p_number" value=1}
        {assign var="products_amount" value=0}
        {assign var="count_page" value=1}
        {assign var="price_products" value=0}
        {foreach from=$order_info.products item=product}
            <span style="position: absolute; height: 5mm; width: 7mm; top: {$p_size+51.5}mm; left: 20mm; text-align: center; font: 11pt 'Arial';">{$p_number}</span>
            <span style="position: absolute; height: 5mm; width: 63mm; top: {$p_size+51.5}mm; left: 27mm; font: 6pt 'Arial';">
                {$product.product}
                {hook name="orders:product_info"}
                {/hook}
            </span>
            <span style="position: absolute; height: 5mm; width: 14mm; top: {$p_size+51.5}mm; left: 90mm; text-align: center; font: 11pt 'Arial';">{$product.amount}</span>
            <span style="position: absolute; height: 5mm; width: 25mm; top: {$p_size+51.5}mm; left: 105mm; text-align: center; font: 11pt 'Arial';">{$product.amount * $product.price}</span>

            <span style="position: absolute; height: 5mm; width: 7mm; top: {$p_size+51.5}mm; left: 165mm; text-align: center; font: 11pt 'Arial';">{$p_number}</span>
            <span style="position: absolute; height: 5mm; width: 63mm; top: {$p_size+51.5}mm; left: 172mm; font: 6pt 'Arial';">
                {$product.product}
                {hook name="orders:product_info"}
                {/hook}
            </span>
            <span style="position: absolute; height: 5mm; width: 14mm; top: {$p_size+51.5}mm; left: 235mm; text-align: center; font: 11pt 'Arial';">{$product.amount}</span>
            <span style="position: absolute; height: 5mm; width: 25mm; top: {$p_size+51.5}mm; left: 250mm; text-align: center; font: 11pt 'Arial';">{$product.amount * $product.price}</span>

            {$price_products = $price_products + $product.amount * $product.price}
            {$products_amount = $products_amount + $product.amount}

            {$p_size = $p_size + 5.5}
            {$p_number = $p_number + 1}
            {if ($p_number - ($count_page * 14)) == 1}
                {if $data.print_bg == 'Y'}
                    <img style="width: 293mm; height: 205mm;" src="{$images_dir}/addons/rus_russianpost/blank_107.jpg" />
                {/if}
                {$p_size = $p_size + 128.5}
                <span style="position: absolute; height: 10mm; width: 80mm; top: {$p_size+142}mm; left: 20mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fio} {$data.fio2}{/if}</span>
                <span style="position: absolute; height: 10mm; width: 80mm; top: {$p_size+142}mm; left: 165mm; font: 11pt 'Arial';">{if $data.sender == '1'}{$data.whom} {$data.whom2}{else}{$data.fio} {$data.fio2}{/if}</span>
                {$count_page = $count_page + 1}
            {/if}
        {/foreach}

        {$p_number = $p_number - 1}
        {$p_size = $p_size + (14 * $count_page - $p_number) * 5.5 + 51.5}
        <span style="position: absolute; height: 5mm; width: 14mm; top: {$p_size}mm; left: 90mm; font: 11pt 'Arial'; text-align: center;">{$products_amount}</span>
        <span style="position: absolute; height: 5mm; width: 14mm; top: {$p_size}mm; left: 235mm; font: 11pt 'Arial'; text-align: center;">{$products_amount}</span>

        <span style="position: absolute; height: 5mm; width: 25mm; top: {$p_size}mm; left: 105mm; font: 11pt 'Arial'; text-align: center;">{$price_products}</span>
        <span style="position: absolute; height: 5mm; width: 25mm; top: {$p_size}mm; left: 250mm; font: 11pt 'Arial'; text-align: center;">{$price_products}</span>
    </div>
</body>
</html>
