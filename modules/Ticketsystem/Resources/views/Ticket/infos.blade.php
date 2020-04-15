<table class='table table-bordered table-hover'>
    @foreach ($infos as $key => $value)
        <tr>
            <td>{{ $key }}</td>
            <td>{{ $value }}</td>
        </tr>
    @endforeach
</table>
