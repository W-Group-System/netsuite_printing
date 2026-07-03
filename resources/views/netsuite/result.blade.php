@extends('layouts.header')
@section('content')


<div class="col-12 grid-margin stretch-card">
  <div class="card">
    <div class="card-body">
      <div class="form-group">
        <form method="GET" action="{{ url('/netsuite/search') }}">
          <div class="input-group">
              <input type="text" class="form-control" name="tranid" value="{{ request('tranid') }}" placeholder="Enter SI number">
              <div class="input-group-append">
                <button class="btn btn-sm btn-primary" type="submit">Search</button>
              </div>
          </div>
        </form>
        </div>         
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
              <th>Subsidiary</th>
              <th>Status</th>
              <th>Period</th>
              <th>TXN No.</th>
              <th>Reference No</th>
              <th>MEMO / PARTICULARS</th>
              <th>Amount</th>
              <th>Check Number</th>
            </thead>
            <tbody>
              @if(!empty($results))
              {{-- {{ dd($results) }} --}}
                  @foreach($results as $result)
                  <tr>
                    <td>
                      {{-- <button onclick="" type="button" class="btn btn-md btn-icon btn-success" title="Edit" data-toggle="modal" data-target="#create_ap{{ $result['id'] ?? '' }}" ><i class="ti-pencil-alt"></i></button>
                      @include('netsuite.new_ap') --}}
                      @if(isset($apVoucherData[$result['id']]))
                          <button type="button"
                                  class="btn btn-md btn-icon btn-success"
                                  title="Edit"
                                  data-toggle="modal"
                                  data-target="#edit_ap{{ $result['id'] }}">
                              <i class="ti-pencil-alt"></i>
                          </button>
                          <button type="button"
                              class="btn btn-md btn-icon btn-danger"
                              onclick="window.open('{{ url('ap_voucher_print', $result['id']) }}', '_blank')">
                              <i class="ti-printer"></i>
                          </button>

                          @include('netsuite.edit_ap')
                      @else
                          <button type="button"
                                  class="btn btn-md btn-icon btn-primary"
                                  title="Create"
                                  data-toggle="modal"
                                  data-target="#create_ap{{ $result['id'] }}">
                              <i class="ti-pencil-alt"></i>
                          </button>

                          @include('netsuite.new_ap')
                      @endif
                      
                      
                    </td>
                    <td>{{ $result['id'] ?? '' }}</td>
                    <td>{{ $result['subsidiary']['refName'] ?? '' }}</td>
                    <td>{{ $result['status']['refName'] ?? '' }}</td>
                    <td>{{ $result['postingPeriod']['refName'] ?? '' }}</td>
                    <td>{{ $result['transactionNumber'] ?? '' }}</td>
                    <td>{{ $result['tranId'] ?? '' }}</td>
                    <td>{{ $result['memo'] ?? '' }}</td>
                    <td>{{ $result['total'] ?? '' }}</td>
                    <td>{{ $result['custbody_wg_payingtrxn'] ?? '' }}</td>
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



{{-- {{dd($results)}} --}}

{{-- @if(isset($result))
{{ dd($result) }}
    <h3>Result</h3>

    <p>Tran ID: {{ $result['tranId'] }}</p>
    <p>Total: {{ $result['total'] }}</p>
    <p>Date: {{ $result['trandate'] }}</p>
    <p>Entity: {{ $result['entity'] }}</p>
@else
    <p>No result found</p>
@endif --}}
@endsection


