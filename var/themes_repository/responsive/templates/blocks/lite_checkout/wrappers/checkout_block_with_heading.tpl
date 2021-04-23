{if $content|trim}
    <div class="litecheckout__container">
        <div class="litecheckout__group">
            <div class="litecheckout__item">
                <h2 class="litecheckout__step-title">{$block.name}</h2>
            </div>
        </div>
        {$content nofilter}
    </div>
{/if}