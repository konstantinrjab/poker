<?php
/** @var array $data */
?>
<table cellpadding="0" cellspacing="0" border="0"
       style="table-layout: fixed;border-collapse: collapse;border-color: rgb(115, 98, 98);border-style: solid;border-width: 1px 1px 1px;">
    <tbody>
    @foreach ($data as $key => $value):
    <tr>
        <td style="border-collapse: collapse; color: rgb(82,82,82); font-family: 'Helvetica Neue',Arial,sans-serif; font-size: 15px; line-height: 22px; padding: 10px;border-color: rgb(115, 98, 98);border-style: solid;border-width: 1px 1px 1px;">
            {{ $key }}
        </td>
        <td style="border-collapse: collapse; color: rgb(82,82,82); font-family: 'Helvetica Neue',Arial,sans-serif; font-size: 15px; line-height: 22px; padding: 10px;border-color: rgb(115, 98, 98);border-style: solid;border-width: 1px 1px 1px;">
            {{ $value }}
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
