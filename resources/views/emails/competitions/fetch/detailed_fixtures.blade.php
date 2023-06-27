@extends('emails.layouts.app')
@section('content')
    <h2 class="text-gray-700 dark:text-gray-200">{{ $data['name'] }}</h2>

    <p style="font-size:16px">
        The website fetches competition fixtures in groups of {{ $data['chunk'] }}, utilizing memory-efficient techniques to
        handle large datasets by retrieving and processing records in smaller, manageable portions.
    </p>

    @foreach ($data['competitions_and_teams'] as $key => $competitions_and_teams)
        <h4>Competition: {{ $competitions_and_teams['competition'] }}</h4>
        <ul>
            @foreach ($competitions_and_teams['teams'] as $team)
                <li>
                    <table>
                        <tr>
                            <th colspan="2">
                                {{ $team['teams']['home_team_id'] . ' vs ' . $team['teams']['away_team_id'] }}
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2">
                                Game details: {{ $team['fetch_details']['game_details'] }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <b>Head 2 Head:</b>
                                <table>
                                    @foreach ($team['fetch_details']['game_h2h'] as $key => $value)
                                        <tr>
                                            <td colspan="2">
                                                {{ $value['name'] }}, {{ $value['counts'] }} times.
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>

                    </table>
                </li>
            @endforeach
        </ul>
    @endforeach

    <p class="mt-2 text-gray-600 dark:text-gray-300 font-bold text-lg">
        {{ $data['message'] }}
    </p>
    @php
        $game_info = $data['games_info'];
    @endphp
    <div style="border: solid 1px solid gray">
        <h4>Table: {{ $data['table'] }}, competitions counts in this chunk: {{ $game_info['competitions'] }}</h4>
        @php
            $all_games = $game_info['all_games'];
            $untouched_games = $game_info['untouched_games'];
            $touched_games = $all_games - $untouched_games;
        @endphp
        <p>All games: {{ $all_games }}</p>
        <p>Touched games: {{ $touched_games }} ({{ round(($touched_games / $all_games) * 100) }}%)</p>
        <p>Untouched games: {{ $untouched_games }} ({{ round(($untouched_games / $all_games) * 100) }}%)</p>
    </div>

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
