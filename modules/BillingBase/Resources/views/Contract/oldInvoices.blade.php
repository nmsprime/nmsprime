<table class="table">
  @foreach ($invoices as $key => $invoice)
    <?php $labelData = $invoice->view_index_label();
        // Split invoice table in 2 columns
        if ($key % 2) {
            continue;
        }
     ?>
    <tr>
      <td class="{{ $labelData['bsclass'] }}" align="center">
        {!! $invoice->view_icon() !!} {!! HTML::linkRoute('Invoice.edit', is_array($labelData) ? $labelData['header'] : $labelData, $invoice->id) !!}
      </td>

      @if(isset($invoices[$key+1]))
        <?php $labelData = $invoices[$key+1]->view_index_label(); ?>
        <td class="{{ $labelData['bsclass'] }}" align="center">
          {!! $invoices[$key+1]->view_icon() !!} {!! HTML::linkRoute('Invoice.edit', is_array($labelData) ? $labelData['header'] : $labelData, $invoices[$key+1]->id) !!}
        </td>
      @endif

    </tr>
  @endforeach
</table>
