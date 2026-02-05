<style>
    .sl-stats { margin-bottom: 15px; }
    .sl-stats .row { display: flex; flex-wrap: wrap; }
    .sl-stats .row > div { display: flex; }
    .sl-stat-box { background: #fff; border: 1px solid #ddd; border-left: 3px solid #6B3C00; padding: 10px 15px; margin-bottom: 10px; flex: 1; min-height: 70px; }
    .sl-stat-box .val { font-size: 24px; font-weight: 600; color: #333; }
    .sl-stat-box .lbl { font-size: 11px; color: #888; text-transform: uppercase; }
    .sl-stat-box .sub { font-size: 11px; color: #666; margin-top: 3px; }
    .sl-grade-bar { display: flex; height: 20px; overflow: hidden; background: #eee; }
    .sl-grade-bar .seg { display: flex; align-items: center; justify-content: center; color: #fff; font-size: 10px; }
    .sl-grade-legend { font-size: 11px; color: #666; margin-top: 8px; }
    .sl-grade-legend span { display: inline-block; width: 10px; height: 10px; margin-right: 3px; vertical-align: middle; }
</style>

<div class="sl-stats">
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="sl-stat-box">
                <div class="val">{{ number_format($totalCount) }}</div>
                <div class="lbl">Total Slaughters</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="sl-stat-box">
                <div class="val">{{ $todayCount }} / {{ $weekCount }} / {{ $monthCount }}</div>
                <div class="lbl">Today / Week / Month</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="sl-stat-box">
                <div class="val">{{ number_format($totalWeight, 0) }} <small style="font-size:12px;">kg</small></div>
                <div class="lbl">Total Weight</div>
                <div class="sub">Avg: {{ $avgWeight }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="sl-stat-box">
                <div class="val">{{ $completedCount }} / {{ $ongoingCount }}</div>
                <div class="lbl">Completed / Ongoing</div>
                <div class="sub">{{ $completionRate }}% done</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="sl-stat-box">
                <div class="val">{{ number_format($totalCuts) }}</div>
                <div class="lbl">Cuts Created</div>
                <div class="sub">{{ number_format($cutsWeight, 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="sl-stat-box">
                <div class="val">{{ number_format($availableWeight, 0) }} <small style="font-size:12px;">kg</small></div>
                <div class="lbl">Available Weight</div>
            </div>
        </div>
    </div>
    <div class="row" style="display:block;">
        <div class="col-md-12">
            @php
                $gradeTotal = $gradeA + $gradeB + $gradeC + $gradeOther + $notGraded;
                $pctA = $gradeTotal > 0 ? ($gradeA / $gradeTotal) * 100 : 0;
                $pctB = $gradeTotal > 0 ? ($gradeB / $gradeTotal) * 100 : 0;
                $pctC = $gradeTotal > 0 ? ($gradeC / $gradeTotal) * 100 : 0;
                $pctOther = $gradeTotal > 0 ? ($gradeOther / $gradeTotal) * 100 : 0;
                $pctNone = $gradeTotal > 0 ? ($notGraded / $gradeTotal) * 100 : 0;
            @endphp
            <div class="sl-grade-bar">
                <div class="seg" style="width:{{ $pctA }}%;background:#6B3C00;" title="Grade A: {{ $gradeA }}"></div>
                <div class="seg" style="width:{{ $pctB }}%;background:#8B5A1B;" title="Grade B: {{ $gradeB }}"></div>
                <div class="seg" style="width:{{ $pctC }}%;background:#A67C3D;" title="Grade C: {{ $gradeC }}"></div>
                <div class="seg" style="width:{{ $pctOther }}%;background:#999;" title="Other: {{ $gradeOther }}"></div>
                <div class="seg" style="width:{{ $pctNone }}%;background:#ccc;" title="Not Graded: {{ $notGraded }}"></div>
            </div>
            <div class="sl-grade-legend">
                <span style="background:#6B3C00;"></span>A: {{ $gradeA }} &nbsp;
                <span style="background:#8B5A1B;"></span>B: {{ $gradeB }} &nbsp;
                <span style="background:#A67C3D;"></span>C: {{ $gradeC }} &nbsp;
                <span style="background:#999;"></span>Other: {{ $gradeOther }} &nbsp;
                <span style="background:#ccc;"></span>Not Graded: {{ $notGraded }}
            </div>
        </div>
    </div>
</div>
