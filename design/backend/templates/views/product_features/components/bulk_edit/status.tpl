{foreach $product_feature_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form"
            href="{"product_features.m_update_statuses?status={$status}"|fn_url}"
            data-ca-target-id="update_features_list"
            data-ca-target-form="#manage_product_features_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}
