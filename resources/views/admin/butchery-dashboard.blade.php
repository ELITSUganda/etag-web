<div class="row">
    <!-- Slaughter Overview -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-cut"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Slaughters</span>
                <span class="info-box-number">{{ $totalSlaughters }}</span>
                <small>{{ $completedSlaughters }} Completed | {{ $ongoingSlaughters }} Ongoing</small>
            </div>
        </div>
    </div>
    
    <!-- Distributions/Quarters -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fa fa-cubes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Quarters/Distributions</span>
                <span class="info-box-number">{{ $totalDistributions }}</span>
                <small>{{ number_format($totalDistributedWeight, 1) }} Kgs Distributed</small>
            </div>
        </div>
    </div>
    
    <!-- Carcass Weight -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-balance-scale"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Carcass Weight</span>
                <span class="info-box-number">{{ number_format($totalCarcassWeight, 1) }} Kgs</span>
                <small>{{ number_format($availableCarcassWeight, 1) }} Kgs Available</small>
            </div>
        </div>
    </div>
    
    <!-- Butcher Records -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Butcher Records</span>
                <span class="info-box-number">{{ $totalButcherRecords }}</span>
                <small>{{ $soldRecords }} Sold | {{ $availableRecords }} Available</small>
            </div>
        </div>
    </div>
</div>

<!-- Cut Types Details -->
<div class="row">
    <!-- Prime Cuts -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ $primeCuts }}</h3>
                <p>Prime Cuts</p>
                <small style="font-size: 12px; display: block; margin-top: 5px;">
                    {{ $soldPrimeCuts }} Sold | UGX {{ number_format($primeRevenue) }}
                </small>
            </div>
            <div class="icon">
                <i class="fa fa-trophy"></i>
            </div>
            <a href="{{ admin_url('butcher-records?cut_type=Prime Cut') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Offal Cuts -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>{{ $offalCuts }}</h3>
                <p>Offal Cuts</p>
                <small style="font-size: 12px; display: block; margin-top: 5px;">
                    {{ $soldOffalCuts }} Sold | UGX {{ number_format($offalRevenue) }}
                </small>
            </div>
            <div class="icon">
                <i class="fa fa-heart"></i>
            </div>
            <a href="{{ admin_url('butcher-records?cut_type=Offal Cut') }}" class="small-box-footer">
                View Details <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Revenue -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>UGX {{ number_format($totalRevenue) }}</h3>
                <p>Total Revenue</p>
                <small style="font-size: 12px; display: block; margin-top: 5px;">
                    {{ number_format($soldWeight, 1) }} Kgs Sold
                </small>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
            <a href="{{ admin_url('butcher-records?is_sold=Yes') }}" class="small-box-footer">
                View Sales <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Available Stock -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="small-box bg-orange">
            <div class="inner">
                <h3>{{ number_format($availableWeight, 1) }} Kgs</h3>
                <p>Available Stock</p>
                <small style="font-size: 12px; display: block; margin-top: 5px;">
                    {{ $availableRecords }} Records Available
                </small>
            </div>
            <div class="icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <a href="{{ admin_url('butcher-records?is_sold=No') }}" class="small-box-footer">
                View Available <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Recent Records and Top Cuts -->
<div class="row">
    <!-- Recent Slaughter Records -->
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cut"></i> Recent Slaughter Records</h3>
                <div class="box-tools pull-right">
                    <a href="{{ admin_url('slaughter-records') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
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
                        @forelse($recentSlaughters as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y') }}</td>
                            <td><strong>{{ $record->e_id }}</strong></td>
                            <td>{{ $record->sex }}</td>
                            <td>{{ $record->breed }}</td>
                            <td><span class="label label-info">{{ $record->post_grade }}</span></td>
                            <td>{{ number_format($record->post_weight, 1) }}</td>
                            <td>{{ number_format($record->available_weight, 1) }}</td>
                            <td>
                                @if($record->available_weight == 0)
                                    <span class="label label-success">Completed</span>
                                @else
                                    <span class="label label-warning">Ongoing</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No slaughter records yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Cut Types -->
    <div class="col-md-4">
        <!-- Top Prime Cuts -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-trophy"></i> Top Prime Cuts</h3>
            </div>
            <div class="box-body">
                <ul class="products-list product-list-in-box">
                    @forelse($topPrimeCuts as $cut)
                    <li class="item">
                        <div class="product-info">
                            <a href="{{ admin_url('butcher-records?prime_cut_type=' . urlencode($cut->prime_cut_type)) }}" class="product-title">
                                {{ $cut->prime_cut_type }}
                                <span class="label label-info pull-right">{{ $cut->total }} records</span>
                            </a>
                        </div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-title text-muted">No prime cuts recorded yet</span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Top Offal Cuts -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-heart"></i> Top Offal Cuts</h3>
            </div>
            <div class="box-body">
                <ul class="products-list product-list-in-box">
                    @forelse($topOffalCuts as $cut)
                    <li class="item">
                        <div class="product-info">
                            <a href="{{ admin_url('butcher-records?offal_cut_type=' . urlencode($cut->offal_cut_type)) }}" class="product-title">
                                {{ $cut->offal_cut_type }}
                                <span class="label label-warning pull-right">{{ $cut->total }} records</span>
                            </a>
                        </div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-title text-muted">No offal cuts recorded yet</span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales -->
<div class="row">
    <div class="col-xs-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-money"></i> Recent Sales</h3>
                <div class="box-tools pull-right">
                    <a href="{{ admin_url('butcher-records?is_sold=Yes') }}" class="btn btn-sm btn-success">View All Sales</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date Sold</th>
                            <th>Barcode</th>
                            <th>Cut Type</th>
                            <th>Cut Name</th>
                            <th>Weight Sold (Kgs)</th>
                            <th>Price (UGX)</th>
                            <th>Buyer</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->sold_date ? \Carbon\Carbon::parse($sale->sold_date)->format('d/m/Y') : 'N/A' }}</td>
                            <td><code>{{ substr($sale->barcode, 0, 15) }}...</code></td>
                            <td>
                                @if($sale->cut_type == 'Prime Cut')
                                    <span class="label label-primary">Prime Cut</span>
                                @else
                                    <span class="label label-warning">Offal Cut</span>
                                @endif
                            </td>
                            <td>{{ $sale->cut_type == 'Prime Cut' ? $sale->prime_cut_type : $sale->offal_cut_type }}</td>
                            <td><strong>{{ number_format($sale->original_weight - $sale->current_weight, 2) }}</strong></td>
                            <td><strong>{{ number_format($sale->sold_price) }}</strong></td>
                            <td>{{ $sale->buyer_name ?? 'N/A' }}</td>
                            <td>
                                @if($sale->slaughterDistributionRecord && $sale->slaughterDistributionRecord->animal)
                                    {{ $sale->slaughterDistributionRecord->animal->source_name ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No sales recorded yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
