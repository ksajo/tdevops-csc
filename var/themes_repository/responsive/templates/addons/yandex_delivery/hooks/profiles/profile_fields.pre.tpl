{assign var="data_id" value=$data_id|substr: 2}
{assign var="_pos" value=$_class|strpos:' cm-yad'}

{if $_pos !== false}
    {assign var="_class" value=$_class|substr:0:$_pos scope="parent"}
{/if}

{if $data_id == 'address' || $data_id == 'city' || $data_id == 'zipcode'}
    {assign var="_class" value=$_class|cat:' cm-yad-'|cat:$data_id scope="parent"}
{/if}

