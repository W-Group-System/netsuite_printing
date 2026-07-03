@php
    $apVoucher = $apVoucherData[$result['id']] ?? null;
@endphp

@if($apVoucher)
<div class="modal fade" id="edit_ap{{ $result['id'] }}" tabindex="-1">
    <form method="POST"
          action="{{ url('update_ap_voucher/'.$apVoucher->id) }}"
          autocomplete="off" onsubmit="show()">
        @csrf
        @method('PUT')

        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Edit AP Voucher ({{ $result['transactionNumber'] }})
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label><strong>Invoice / Billing Date</strong></label>
                        <input
                            type="date"
                            class="form-control"
                            name="invoice_billing_date"
                            value="{{ $apVoucher->invoice_billing_date }}">
                    </div>

                    <div class="form-group">
                        <label><strong>Front Office Received Date</strong></label>
                        <input
                            type="date"
                            class="form-control"
                            name="bmo_received_date"
                            value="{{ $apVoucher->bmo_received_date }}">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="rush_edit{{ $result['id'] }}"
                                name="rush"
                                value="1"
                                {{ $apVoucher->rush ? 'checked' : '' }}>

                            <label class="custom-control-label"
                                   for="rush_edit{{ $result['id'] }}">
                                <strong>Rush</strong>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="checked_by"><strong>Checked by</strong></label>
                        <select class="form-control js-example-basic-single" name="checked_by" id="checked_by{{ $result['id'] }}" style="width: 100%" required>
                        <option value="" disabled selected>Select Checked by</option>
                        @foreach($checkers as $checker)
                            <option value="{{ $checker->id }}" {{ optional($apVoucher->checkers)->id == $checker->id ? 'selected' : '' }}>
                                {{ $checker->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group row">
                        <label for="checked_by"><strong>Approved by</strong></label>
                        <select class="form-control js-example-basic-single" name="approved_by" id="approved_by{{ $result['id'] }}" style="width: 100%" required>
                        <option value="" disabled selected>Select Approved by</option>
                        @foreach($approvers as $approver)
                            <option value="{{ $approver->id }}" {{ optional($apVoucher->approvers)->id == $approver->id ? 'selected' : '' }}>{{ $approver->name }}</option>
                        @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button class="btn btn-primary">
                        Update
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endif