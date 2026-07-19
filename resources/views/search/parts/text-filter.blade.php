{{--
@filters - Active search filters
@name - Name of filter
--}}
<table class="no-style form-table mb-xs">
    <tr>
        <td width="200">{{ trans('entities.search_' . $name) }}</td>
        <td width="80"></td>
    </tr>
    <tr component="optional-input">
        <td>
            <button type="button" refs="optional-input@show"
                    class="text-button {{ ($filters[$name] ?? false) ? 'hidden' : '' }}">{{ trans('entities.search_set_value') }}</button>
            <input class="tag-input {{ ($filters[$name] ?? false) ? '' : 'hidden' }}"
                   refs="optional-input@input"
                   value="{{ $filters[$name] ?? '' }}"
                   type="text"
                   name="filters[{{ $name }}]">
        </td>
        <td>
            <button type="button"
                    refs="optional-input@remove"
                    class="text-neg text-button {{ ($filters[$name] ?? false) ? '' : 'hidden' }}">
                @icon('close')
            </button>
        </td>
    </tr>
</table>
