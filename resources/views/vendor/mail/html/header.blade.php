@props(['url'])
{{--
    The published Laravel default rendered a remote image from laravel.com when
    the slot was the string 'Laravel'. That branch is gone: emails should never
    depend on an external asset, and the header always shows this chain's name,
    which arrives here as config('app.name').
--}}
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{ $slot }}
</a>
</td>
</tr>
