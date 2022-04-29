<div class="row">
    <div class="col-xl-3">
        <div class="card">
            <div class="card-body pb-0" style="position: relative;">
                <h5 class="card-title mb-0 header-title">All games</h5>

                @php
                    $xAxis = []; $data = [];
                    for($i = 7; $i >= 0; $i--) {
                        array_push($xAxis, $i > 0 ? $i.' days ago' : 'Today');
                        array_push($data, \Illuminate\Support\Facades\DB::table('games')->where('created_at', '>=', \Carbon\Carbon::today()->subDays($i))
                            ->where('created_at', '<', \Carbon\Carbon::today()->subDays($i - 1))->count());
                    }

                    $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                    $chart->setType('area')->setHeight(300)->setXAxis($xAxis)->setDataset([[
                        'name' => 'Games',
                        'data' => $data
                    ]]);
                @endphp
                <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                {{ $chart->script() }}
            </div>
        </div>
    </div>
    @foreach(\App\Games\Kernel\Game::list() as $game)
        @if($game->metadata()->isPlaceholder()) @continue @endif
        <div class="col-xl-3">
            <div class="card">
                <div class="card-body pb-0" style="position: relative;">
                    <h5 class="card-title mb-0 header-title">{{ $game->metadata()->name() }}</h5>

                    @php
                        $xAxis = []; $data = [];
                        for($i = 7; $i >= 0; $i--) {
                            array_push($xAxis, $i > 0 ? $i.' days ago' : 'Today');
                            array_push($data, \Illuminate\Support\Facades\DB::table('games')->where('game', $game->metadata()->id())
                                ->where('created_at', '>=', \Carbon\Carbon::today()->subDays($i))
                                ->where('created_at', '<', \Carbon\Carbon::today()->subDays($i - 1))->count());
                        }

                        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart();
                        $chart->setType('area')->setHeight(300)->setXAxis($xAxis)->setDataset([[
                            'name' => 'Games',
                            'data' => $data
                        ]]);
                    @endphp
                    <div id="{{ $chart->id }}" data-chart="m3" class="apex-charts mt-3"></div>
                    {{ $chart->script() }}
                </div>
            </div>
        </div>
    @endforeach
</div>
