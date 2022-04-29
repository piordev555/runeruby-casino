@php
    $humanDiff = function(array $array) {
        foreach($array as $value) $array[array_search($value, $array)] = \Carbon\Carbon::parse($value)->toFormattedDateString();
        return $array;
    };
@endphp

<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body pb-0" style="position: relative">
                <h5 class="card-title mb-0 header-title">Visitors</h5>

                @php
                    $data = \Spatie\Analytics\AnalyticsFacade::fetchTotalVisitorsAndPageViews(\Spatie\Analytics\Period::months(1))->toArray();
                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setType('line')->setHeight(680)->setXAxis($humanDiff(array_column($data, 'date')))->setDataset([
                        [
                            'name' => 'Visitors',
                            'data' => array_column($data, 'visitors')
                        ],
                        [
                            'name' => 'Page views',
                            'data' => array_column($data, 'pageViews')
                        ]
                    ]);
                @endphp
                <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                {{ $chart->script() }}
            </div>
        </div>
    </div>
    <div class="row col-xl-6">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body pb-0" style="position: relative;">
                    <h5 class="card-title mb-0 header-title">Most popular pages</h5>

                    @php
                        $data = \Spatie\Analytics\AnalyticsFacade::fetchMostVisitedPages(\Spatie\Analytics\Period::months(1))->toArray();
                        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                        $chart->setType('bar')->setHeight(300)->setXAxis(array_column($data, 'url'))->setDataset([
                            [
                                'name' => 'Views',
                                'data' => array_column($data, 'pageViews')
                            ]
                        ]);
                    @endphp
                    <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                    {{ $chart->script() }}
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body pb-0" style="position: relative;">
                    <h5 class="card-title mb-0 header-title">HTTP Referer</h5>

                    @php
                        $data = \Spatie\Analytics\AnalyticsFacade::fetchTopReferrers(\Spatie\Analytics\Period::months(1))->toArray();
                        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                        $chart->setType('bar')->setHeight(300)->setXAxis(array_column($data, 'url'))->setDataset([
                            [
                                'name' => 'Views',
                                'data' => array_column($data, 'pageViews')
                            ]
                        ]);
                    @endphp
                    <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                    {{ $chart->script() }}
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body pb-0" style="position: relative;">
                    <h5 class="card-title mb-0 header-title">Most popular browsers</h5>

                    @php
                        $data = \Spatie\Analytics\AnalyticsFacade::fetchTopBrowsers(\Spatie\Analytics\Period::months(1))->toArray();
                        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                        $chart->setType('bar')->setHeight(300)->setXAxis(array_column($data, 'browser'))->setDataset([
                            [
                                'name' => 'Sessions',
                                'data' => array_column($data, 'sessions')
                            ]
                        ]);
                    @endphp
                    <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                    {{ $chart->script() }}
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body pb-0" style="position: relative;">
                    <h5 class="card-title mb-0 header-title">Users</h5>

                    @php
                        $data = \Spatie\Analytics\AnalyticsFacade::fetchUserTypes(\Spatie\Analytics\Period::months(1))->toArray();
                        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                        $chart->setType('pie')->setHeight(300)->setLabels(array_column($data, 'type'))->setDataset(array_column($data, 'sessions'));
                    @endphp
                    <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                    {{ $chart->script() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="media p-3">
                    <div class="media-body">
                        <span class="text-muted text-uppercase font-size-12 font-weight-bold">Users</span>
                        <h2 class="mb-0">{{ \App\User::where('bot', '!=', true)->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-6">
                        <div class="media p-3">
                            <div class="media-body">
                                <span class="text-muted text-uppercase font-size-12 font-weight-bold">Total referrals</span>
                                <h2 class="mb-0">{{ \Illuminate\Support\Facades\DB::table('users')->where('referral', '!=', null)->count() }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="media p-3">
                            <div class="media-body">
                                <span class="text-muted text-uppercase font-size-12 font-weight-bold">Active referrals</span>
                                <h2 class="mb-0">
                                    @php
                                        $count = 0;
                                        foreach(\App\User::where('referral', '!=', null)->get() as $referral) {
                                            $user = \App\User::where('_id', $referral->referral)->first();
                                            if(in_array($referral->_id, $user->referral_wager_obtained ?? [])) $count++;
                                        }
                                    @endphp
                                    {{ $count }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body pb-0">
                <ul class="nav card-nav float-right">
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=today]').show(); window.dispatchEvent(new Event('resize'));">Today</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=week]').show(); window.dispatchEvent(new Event('resize'));">7d</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=d15]').show(); window.dispatchEvent(new Event('resize'));">15d</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=m1]').show(); window.dispatchEvent(new Event('resize'));">1m</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=m3]').show(); window.dispatchEvent(new Event('resize'));">3m</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=m6]').show(); window.dispatchEvent(new Event('resize'));">6m</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="javascript:void(0)" onclick="$('[data-chart-u]').hide(); $('[data-chart-u=y]').show(); window.dispatchEvent(new Event('resize'));">1y</a>
                    </li>
                </ul>
                <h5 class="card-title mb-0 header-title">New Users</h5>

                @php
                    $fill_data = function() {
                        $out = [];

                        for($i = 0; $i <= 23; $i++) {
                            if (\Carbon\Carbon::now()->timestamp < \Carbon\Carbon::today()->addHours($i)->timestamp) continue;
                            array_push($out, \Illuminate\Support\Facades\DB::table('users')->where('created_at', '>=', \Carbon\Carbon::today()->addHours($i))->where('bot', '!=', true)
                                ->where('created_at', '<=', \Carbon\Carbon::today()->addHours($i+1))->count());
                        }
                        return $out;
                    };
                    $fill_labels = function() {
                        $out = [];
                        for($i = 0; $i <= 23; $i++) {
                            if (\Carbon\Carbon::now()->timestamp < \Carbon\Carbon::today()->addHours($i)->timestamp) continue;
                            array_push($out, $i.':00 - '.$i.':59');
                        }
                        return $out;
                    };

                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('Today')->setType('area')->setHeight(600)->setXAxis($fill_labels())->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data()
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="today" class="apex-charts mt-3"></div>
                {{ $chart->script() }}

                @php
                    $fill_data = function($days) {
                        $out = [];
                        for($i = 0; $i < $days; $i++)
                            array_push($out, \Illuminate\Support\Facades\DB::table('users')->where('created_at', '>=', \Carbon\Carbon::today()->subDays($i + 1))
                                    ->where('created_at', '<=', \Carbon\Carbon::today()->subDays($i))->count());
                        return array_reverse($out);
                    };
                    $fill_labels = function($days) {
                        $out = [];
                        for($i = 0; $i < $days; $i++)
                            array_push($out, $i > 0 ? $i .' days ago' : 'Today');
                        return array_reverse($out);
                    };

                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('7 days')->setType('area')->setHeight(600)->setXAxis($fill_labels(7))->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data(7)
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="week" class="apex-charts mt-3" style="display: none"></div>
                {{ $chart->script() }}

                @php
                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('15 days')->setType('area')->setHeight(600)->setXAxis($fill_labels(15))->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data(15)
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="d15" class="apex-charts mt-3" style="display: none"></div>
                {{ $chart->script() }}

                @php
                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('30 days')->setType('area')->setHeight(600)->setXAxis($fill_labels(30))->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data(30)
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="m1" class="apex-charts mt-3" style="display: none"></div>
                {{ $chart->script() }}

                @php
                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('3 months')->setType('area')->setHeight(600)->setXAxis($fill_labels(90))->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data(90)
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="m3" class="apex-charts mt-3" style="display: none"></div>
                {{ $chart->script() }}

                @php
                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('6 months')->setType('area')->setHeight(600)->setXAxis($fill_labels(180))->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data(180)
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="m6" class="apex-charts mt-3" style="display: none"></div>
                {{ $chart->script() }}

                @php
                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setTitle('1 year')->setType('area')->setHeight(600)->setXAxis($fill_labels(365))->setDataset([[
                        'name' => 'Total',
                        'data' => $fill_data(365)
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart-u="y" class="apex-charts mt-3" style="display: none"></div>
                {{ $chart->script() }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body pt-2">
                <h6 class="header-title mb-4">Most popular browsers</h6>
                @php
                    $data = (array) \Spatie\Analytics\AnalyticsFacade::performQuery(\Spatie\Analytics\Period::months(1), 'ga:sessions', [
                        'metrics' => 'ga:sessions',
                        'dimensions' => 'ga:browser'
                    ])['rows'];

                    $labels = []; $dataset = [];
                    foreach ($data as $info) {
                        array_push($labels, $info[0]);
                        array_push($dataset, intval($info[1]));
                    }

                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setType('pie')->setHeight(300)->setLabels($labels)->setDataset($dataset);
                @endphp
                <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                {{ $chart->script() }}
            </div>
        </div>
    </div>
</div>
