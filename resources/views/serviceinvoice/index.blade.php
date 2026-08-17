@extends('layouts.header')
@section('content')


<div class="col-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('/service_invoice') }}">
                <div class="form-row align-items-end">
                  <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                        <label>SOA Number</label>
                        <input type="text"
                               class="form-control"
                               name="tranid"
                               value="{{ request('tranid') }}"
                               placeholder="Enter SOA Number">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label>From</label>
                        <input type="date"
                               class="form-control"
                               name="from"
                               value="{{ request('from') }}">
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label>To</label>
                        <input type="date"
                               class="form-control"
                               name="to"
                               value="{{ request('to') }}">
                    </div>

                    <div class="col-auto mb-2">
                        <button class="btn btn-primary btn-sm" type="submit">
                            Search
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<div class="col-12 grid-margin stretch-card">
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover tablewithSearch">
            <thead>
              <th>Action</th>
              <th>Internal ID</th>
              <th>SI No.</th>
              <th>Subsidiary</th>
              <th>Status</th>
              <th>Period</th>
              <th>TXN No.</th>
              <th>Reference No</th>
              <th>MEMO / PARTICULARS</th>
              <th>Amount</th>
            </thead>
            <tbody>
              @if(!empty($results))
                  @foreach($results as $result)
                  <tr>
                    <td>
                      @if(isset($apVoucherData[$result['id']]))
                          <button type="button"
                                  class="btn btn-md btn-icon btn-success"
                                  title="Edit"
                                  data-toggle="modal"
                                  data-target="#edit_ap{{ $result['id'] }}">
                              <i class="ti-pencil-alt"></i>
                          </button>

                          {{-- @include('netsuite.edit_ap') --}}
                      @else
                          {{-- <button type="button"
                                  class="btn btn-md btn-icon btn-primary"
                                  title="Create"
                                  data-toggle="modal"
                                  data-target="#create_ap{{ $result['id'] }}">
                              <i class="ti-pencil-alt"></i>
                          </button> --}}
                          {{-- <button type="button"
                              class="btn btn-md btn-icon btn-danger"
                              onclick="window.open('{{ url('print_service_invoice', $result['id']) }}', '_blank')">
                              <i class="ti-printer"></i>
                          </button> --}}
                          <button type="button"
                              class="btn btn-md btn-icon btn-warning"
                              onclick="window.open('{{ url('print_service_invoice_ar', $result['id']) }}', '_blank')">
                              <i class="ti-receipt"></i>
                          </button>
                          {{-- @include('netsuite.new_ap') --}}
                      @endif
                      
                    </td>
                    <td>{{ $result['id'] ?? '' }}</td>
                    <td>{{ $result['otherrefnum'] ?? '' }}</td>
                    <td>{{ $result['subsidiary']?? '' }}</td>
                    <td>{{ $result['status'] = trim(substr(strrchr($result['status'], ':'), 1)) }}</td>
                    <td>{{ $result['postingperiod'] ?? '' }}</td>
                    <td>{{ $result['transactionnumber'] ?? '' }}</td>
                    <td>{{ $result['tranid'] ?? '' }}</td>
                    <td>{{ $result['memo'] ?? '' }}</td>
                    <td>{{ number_format($result['total'],2) ?? '' }}</td>
                  </tr>
                  @endforeach
              @else
                  <p>No result found</p>
              @endif
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection


