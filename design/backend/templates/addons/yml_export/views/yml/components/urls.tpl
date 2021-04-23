<div class="control-group">
    <h4>{__('information')}</h4>

    {$yml2_categories_information nofilter}
    <br>
    {$yml2_information nofilter}
    {if $yml2_cron_command_information}
        <br>
        <br>
        {$yml2_cron_command_information nofilter}
    {/if}
</div>

