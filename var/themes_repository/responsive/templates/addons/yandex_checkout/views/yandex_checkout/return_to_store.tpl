<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    >
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{__("yandex_checkout.payment_processing")}</title>
    {include file="common/scripts.tpl"}
    {include file="common/styles.tpl"}
    <style>
        .ty-ajax-loading-box-with__text-wrapper {
            font-size: 200%;
            width: auto;
        }
        .ty-ajax-loading-box_text_block {
            display: block;
            min-width: 320px;
            background-position: center 15px;
            margin-left: -160px;
            margin-top: -170px;
        }
    </style>
</head>
<body>
{include file="common/loading_box.tpl"}
<script>
    (function (_, $) {
        var waitingTime = 0;
        var pollingInterval = 5;
        var isRequestComplete = true;

        $.ceEvent('on', 'ce.commoninit', function () {
            $.toggleStatusBox('show', {
                statusContent: '<span class="ty-ajax-loading-box-with__text-wrapper">{__("yandex_checkout.check_payment_status")}</span>',
                statusClass: 'ty-ajax-loading-box_text_block'
            });
            var checkPaymentStatus = setInterval(function () {
                if (!isRequestComplete) {
                    return;
                }
                isRequestComplete = false;
                waitingTime += pollingInterval;
                $.ceAjax('request', '{$check_order_status_url}', {
                    method: 'get',
                    caching: false,
                    hidden: true,
                    keep_status_box: true,
                    data: {
                        waiting_time: waitingTime,
                        order_id: "{$smarty.request.order_id}"
                    },
                    callback: function (response) {
                        isRequestComplete = true;
                        if (response.current_url) {
                            clearInterval(checkPaymentStatus);
                            $.redirect(response.current_url);
                        }
                    }
                });
            }, 1000 * pollingInterval);
        });
    })(Tygh, Tygh.$);
</script>
</body>
</html>