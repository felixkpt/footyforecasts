@extends('emails.layouts.app')
@section('content')
    <h2 class="text-gray-700 dark:text-gray-200">{{ $data['name'] }}</h2>

    <h3 style="font-size:16px">Detailed Competitions fixtures fetch completion notification {{ date('Y-m-d H:i:s') }}</h3>

    <p class="mt-2 leading-loose text-gray-600 dark:text-gray-300 font-bold text-lg">
        {{ $data['message'] }}
    </p>
    <p style="font-size: 16px">
        <h4>Table: {{ $data['table'] }}, competitions info per chunk:</h4>
    <div style="border: solid 1px solid gray">
        <ul>
            @foreach ($data['games_info'] as $game_info)
                <li>
                    <b>Competitions counts: {{ $game_info[0] ?? 0 }}</b>
                    @php
                        $all_games = $game_info['all_games'];
                        $untouched_games = $game_info['untouched_games'];
                        $touched_games = $all_games - $untouched_games;
                    @endphp
                    <p>All games: {{ $all_games }}</p>
                    <p>Touched games: {{ $touched_games }} ({{ round(($touched_games / $all_games) * 100) }}%)</p>
                    <p>Untouched games: {{ $untouched_games }} ({{ round(($untouched_games / $all_games) * 100) }}%)</p>
                </li>
            @endforeach
        </ul>
        @unless ($data['games_info'])
            <div style="font-style:italic">No competitions processed, everything is upto date!</div>
        @endunless
    </div>
    </p>
    <p class="mt-2 text-gray-600 dark:text-gray-300">
        Please review and ensure the smooth functioning of this feature.
    </p>
    <p class="mt-2 text-gray-600 dark:text-gray-300">
        Best regards, <br>
        Felix Biwott Team
    </p>
    <a href="{{ URL::to('') }}"
        class="px-6 py-2 mt-8 text-sm font-medium tracking-wider text-white capitalize transition-colors duration-300 transform bg-blue-600 rounded-lg hover:bg-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-80">
        Visit site
    </a>
@endsection
