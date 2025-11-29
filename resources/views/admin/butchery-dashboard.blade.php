<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-cut"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Slaughters</span>
                <span class="info-box-number">{{ $totalSlaughters }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-paw"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Distributions</span>
                <span class="info-box-number">{{ $totalDistributions }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sold Records</span>
                <span class="info-box-number">{{ $soldRecords }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-inbox"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Available</span>
                <span class="info-box-number">{{ $availableRecords }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $primeCuts }}</h3>
                <p>Prime Cuts</p>
            </div>
            <div class="icon">
                <i class="fa fa-certificate"></i>
            </div>
            <a href="{{ admin_url('butcher-records?cut_type=Prime Cut') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $offalCuts }}</h3>
                <p>Offal Cuts</p>
            </div>
            <div class="icon">
                <i class="fa fa-heart"></i>
            </div>
            <a href="{{ admin_url('butcher-records?cut_type=Offal Cut') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>UGX {{ number_format($totalRevenue) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-orange">
            <div class="inner">
                <h3>{{ number_format($availableWeight, 1) }} Kgs</h3>
                <p>Available Weight</p>
            </div>
            <div class="icon">
                <i class="fa fa-balance-scale"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Recent Slaughter Records</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>E-ID</th>
                            <th>Sex</th>
                            <th>Breed</th>
                            <th>Grade</th>
                            <th>Weight (Kgs)</th>
                            <th>Available (Kgs)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSlaughters as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y') }}</td>
                            <td><strong>{{ $record->e_id }}</strong></td>
                            <td>{{ $record->sex }}</td>
                            <td>{{ $record->breed }}</td>
                            <td>{{ $record->post_grade }}</td>
                            <td>{{ $record->post_weight }}</td>
                            <td>{{ $record->available_weight }}</td>
                            <td>
                                @if(strtolower($record->breed) == 'done')
                                    <span class="label label-success">Completed</span>
                                @else
                                    <span class="label label-warning">Ongoing</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
