@if ( $result)
<div class="modal fade" id="create_ap{{ $result['id'] }}" tabindex="-1" aria-labelledby="ap voucher" aria-hidden="true">
    <form method="POST" id="apvoucheradd" action="{{ url('new_ap_voucher/' .$result['id']) }}" autocomplete="off" onsubmit="show()">
      @csrf
    <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="apVoucher">TXN No ({{ $result['transactionNumber'] }})</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <label for="invoice_billing_date"><strong>Invoice / Billing Date</strong></label>
                <input
                    type="date"
                    class="form-control"
                    id="invoice_billing_date"
                    name="invoice_billing_date">
            </div>

            <div class="form-group mt-3">
                <label for="bmo_received_date"><strong>Front Office Received Date</strong></label>
                <input
                    type="date"
                    class="form-control"
                    id="bmo_received_date"
                    name="bmo_received_date">
            </div>

            <div class="form-group mt-4">
                <div class="custom-control custom-checkbox">
                    <input
                        type="checkbox"
                        class="custom-control-input"
                        id="rush"
                        name="rush"
                        value="1">
                    <label class="custom-control-label" for="rush">
                        <strong>Rush</strong>
                    </label>
                </div>
            </div>

            <div class="form-group row">
                <label for="checked_by"><strong>Checked by</strong></label>
                <select class="form-control js-example-basic-single" name="checked_by" id="checked_by{{ $result['id'] }}" style="width: 100%" required>
                  <option value="" disabled selected>Select Checked by</option>
                  @foreach($checkers as $checker)
                      <option value="{{ $checker->id }}">{{ $checker->name }}</option>
                  @endforeach
                </select>
            </div>
            <div class="form-group row">
                <label for="checked_by"><strong>Approved by</strong></label>
                <select class="form-control js-example-basic-single" name="approved_by" id="approved_by{{ $result['id'] }}" style="width: 100%" required>
                  <option value="" disabled selected>Select Approved by</option>
                  @foreach($approvers as $approver)
                      <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                  @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Ok</button>
          </div>
        </div>
    </div>
    </form>
    </div>
@endif