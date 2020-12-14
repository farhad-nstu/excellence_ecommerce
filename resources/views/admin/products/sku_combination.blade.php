@if(count($combinations) > 0)
    <table class="table table-bordered">
        <thead>
        <tr>
            <td class="text-center">
                <label for="" class="control-label">{{__('Variant')}}</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">{{__('Quantity')}}</label>
            </td>
        </tr>
        </thead> 
        <tbody>
        @foreach ($combinations as $key => $combination)
            @php
                $str = '';
                foreach ($combination as $key => $item)
                {
                    if($key > 0)
                    {
                        $str .= '-'. str_replace(' ', '', $item);
                    }
                    else
                    {
                        $str .= str_replace(' ', '', $item);
                    }
                }
            @endphp
            @if(strlen($str) > 0)
                <tr>
                    <td>
                        <label for="" class="control-label">{{ $str }}</label>
                    </td>
                    <td>
                        <input type="number" name="qty_{{ $str }}" value="10" min="0" step="1" class="form-control" required>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
@endif
